<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Measurement;
use App\Entity\Reservoir;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * State processor for creating measurements.
 * Handles both:
 * - Standard POST /api/measurements
 * - Custom POST /api/reservoirs/{id}/measurements
 * 
 * Automatically sets:
 * - measuredAt to now() if not provided
 * - source to MANUAL for manual creation
 * - reservoir from URL path for custom POST
 * 
 * Security:
 * - User must own the reservoir's farm
 * 
 * @implements ProcessorInterface<Measurement, Measurement>
 */
final class MeasurementPostProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {
    }

    /**
     * Process the measurement creation.
     * 
     * @param Measurement $data The measurement to create
     * @param Operation $operation The API Platform operation
     * @param array<string, mixed> $uriVariables URI variables (e.g., reservoir ID)
     * @param array<string, mixed> $context Additional context
     * @return Measurement The persisted measurement
     * 
     * @throws NotFoundHttpException If reservoir not found (custom POST)
     * @throws AccessDeniedHttpException If user doesn't own the reservoir's farm
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Measurement
    {
        if (!$data instanceof Measurement) {
            throw new \InvalidArgumentException('Expected Measurement entity');
        }

        // For custom POST /api/reservoirs/{id}/measurements
        if (isset($uriVariables['id']) && $operation->getName() === 'reservoir_add_measurement') {
            $reservoirId = $uriVariables['id'];
            $reservoir = $this->entityManager->getRepository(Reservoir::class)->find($reservoirId);
            
            if (!$reservoir) {
                throw new NotFoundHttpException(sprintf('Reservoir with ID %d not found', $reservoirId));
            }
            
            // Security: check ownership
            $user = $this->security->getUser();
            if (!$user || $reservoir->getFarm()->getOwner() !== $user) {
                throw new AccessDeniedHttpException('You do not have permission to add measurements to this reservoir');
            }
            
            // Auto-link to reservoir
            $data->setReservoir($reservoir);
        }
        
        // For standard POST /api/measurements, verify ownership
        if ($data->getReservoir() && $operation->getName() !== 'reservoir_add_measurement') {
            $user = $this->security->getUser();
            if (!$user || $data->getReservoir()->getFarm()->getOwner() !== $user) {
                throw new AccessDeniedHttpException('You do not have permission to add measurements to this reservoir');
            }
        }
        
        // Auto-set measuredAt to now if not provided
        if (!$data->getMeasuredAt()) {
            $data->setMeasuredAt(new \DateTimeImmutable());
        }
        
        // Auto-set source to MANUAL (will be overridden by CSV import)
        if (!$data->getSource() || $data->getSource() === Measurement::SOURCE_MANUAL) {
            $data->setSource(Measurement::SOURCE_MANUAL);
        }
        
        $this->entityManager->persist($data);
        $this->entityManager->flush();
        
        return $data;
    }
}
