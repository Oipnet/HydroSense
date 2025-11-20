<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Dashboard\AlertsSummary;
use App\Dto\Dashboard\DashboardResponse;
use App\Dto\Dashboard\LastMeasurementView;
use App\Dto\Dashboard\ReservoirSummary;
use App\Entity\Alert;
use App\Entity\User;
use App\Repository\AlertRepository;
use App\Repository\MeasurementRepository;
use App\Repository\ReservoirRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * DashboardProvider provides the data for the /api/dashboard endpoint.
 * 
 * This provider:
 * 1. Retrieves the current authenticated user
 * 2. Loads all reservoirs belonging to the user's farms
 * 3. For each reservoir:
 *    - Fetches the last measurement
 *    - Calculates the status based on unresolved alerts (OK/WARN/CRITICAL)
 * 4. Aggregates alert statistics (total, critical, warn)
 * 5. Returns a DashboardResponse DTO
 * 
 * Security: Only accessible by authenticated users (ROLE_USER).
 * Data is automatically scoped to the current user.
 */
class DashboardProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ReservoirRepository $reservoirRepository,
        private readonly MeasurementRepository $measurementRepository,
        private readonly AlertRepository $alertRepository,
    ) {
    }

    /**
     * @return DashboardResponse
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            throw new \RuntimeException('User must be authenticated to access dashboard');
        }

        // Get all reservoirs for the user (through their farms)
        $reservoirs = $this->reservoirRepository->createQueryBuilder('r')
            ->innerJoin('r.farm', 'f')
            ->where('f.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();

        // Get all unresolved alerts for the user
        $unresolvedAlerts = $this->alertRepository->findUnresolvedForUser($user);

        // Group alerts by reservoir ID
        $alertsByReservoir = [];
        foreach ($unresolvedAlerts as $alert) {
            $reservoirId = $alert->getReservoir()->getId();
            if (!isset($alertsByReservoir[$reservoirId])) {
                $alertsByReservoir[$reservoirId] = [];
            }
            $alertsByReservoir[$reservoirId][] = $alert;
        }

        // Build reservoir summaries
        $reservoirSummaries = [];
        foreach ($reservoirs as $reservoir) {
            $reservoirId = $reservoir->getId();

            // Get last measurement for this reservoir
            $lastMeasurement = $this->measurementRepository->createQueryBuilder('m')
                ->where('m.reservoir = :reservoir')
                ->setParameter('reservoir', $reservoir)
                ->orderBy('m.measuredAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            // Create last measurement view
            $lastMeasurementView = null;
            if ($lastMeasurement) {
                $lastMeasurementView = new LastMeasurementView(
                    $lastMeasurement->getMeasuredAt(),
                    $lastMeasurement->getPh(),
                    $lastMeasurement->getEc(),
                    $lastMeasurement->getWaterTemp()
                );
            }

            // Calculate status based on alerts
            $status = $this->calculateReservoirStatus($alertsByReservoir[$reservoirId] ?? []);

            // Create reservoir summary
            $reservoirSummaries[] = new ReservoirSummary(
                $reservoirId,
                $reservoir->getName(),
                $reservoir->getFarm()?->getName(),
                $lastMeasurementView,
                $status
            );
        }

        // Calculate alert summary
        $alertsSummary = $this->calculateAlertsSummary($unresolvedAlerts);

        return new DashboardResponse($reservoirSummaries, $alertsSummary);
    }

    /**
     * Calculate reservoir status based on its unresolved alerts.
     * 
     * @param Alert[] $alerts Unresolved alerts for the reservoir
     * @return string "CRITICAL", "WARN", or "OK"
     */
    private function calculateReservoirStatus(array $alerts): string
    {
        if (empty($alerts)) {
            return 'OK';
        }

        // Check if there's any critical alert
        foreach ($alerts as $alert) {
            if ($alert->getSeverity() === Alert::SEVERITY_CRITICAL) {
                return 'CRITICAL';
            }
        }

        // Check if there's any warning alert
        foreach ($alerts as $alert) {
            if ($alert->getSeverity() === Alert::SEVERITY_WARN) {
                return 'WARN';
            }
        }

        // If only INFO alerts (or empty), return OK
        return 'OK';
    }

    /**
     * Calculate aggregate alert statistics.
     * 
     * @param Alert[] $alerts All unresolved alerts for the user
     * @return AlertsSummary
     */
    private function calculateAlertsSummary(array $alerts): AlertsSummary
    {
        $total = count($alerts);
        $critical = 0;
        $warn = 0;

        foreach ($alerts as $alert) {
            if ($alert->getSeverity() === Alert::SEVERITY_CRITICAL) {
                $critical++;
            } elseif ($alert->getSeverity() === Alert::SEVERITY_WARN) {
                $warn++;
            }
        }

        return new AlertsSummary($total, $critical, $warn);
    }
}
