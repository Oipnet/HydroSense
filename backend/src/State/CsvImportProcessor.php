<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\CsvImportInput;
use App\Entity\Reservoir;
use App\Service\CsvParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * State Processor for handling CSV import of measurements.
 * 
 * This processor handles the POST operation on /api/reservoirs/{id}/measurements/import
 * 
 * CSV Format:
 * - Separator: semicolon (;)
 * - Header: measuredAt;ph;ec;waterTemp
 * - Date format: ISO 8601 (e.g., 2024-11-20T10:30:00 or 2024-11-20 10:30:00)
 * 
 * Response format:
 * {
 *   "success": true,
 *   "imported": 10,
 *   "skipped": 2,
 *   "errors": ["Line 3: Invalid date format", "Line 5: Invalid pH value"]
 * }
 * 
 * Error strategy:
 * - Invalid lines are skipped and reported in the "errors" array
 * - Valid lines are imported even if some lines have errors
 * - If no valid lines are found, throws BadRequestException
 */
class CsvImportProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CsvParserService $csvParser,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param CsvImportInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // Get reservoir ID from URI variables
        $reservoirId = $uriVariables['id'] ?? null;
        
        if (!$reservoirId) {
            throw new BadRequestException('Reservoir ID is required');
        }

        // Find the reservoir
        $reservoir = $this->entityManager->getRepository(Reservoir::class)->find($reservoirId);
        
        if (!$reservoir) {
            throw new NotFoundHttpException('Reservoir not found');
        }

        // Validate that file was uploaded
        if (!$data->file) {
            throw new BadRequestException('No CSV file provided. Please upload a file with the key "file".');
        }

        $file = $data->file;

        // Validate file is a CSV
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new BadRequestException(
                sprintf('Invalid file type. Expected CSV file, got: %s', $mimeType)
            );
        }

        // Read file content
        try {
            $csvContent = file_get_contents($file->getPathname());
            
            if ($csvContent === false || empty($csvContent)) {
                throw new BadRequestException('CSV file is empty or could not be read');
            }
        } catch (\Exception $e) {
            throw new BadRequestException('Failed to read CSV file: ' . $e->getMessage());
        }

        // Parse CSV
        $result = $this->csvParser->parseCsvToMeasurements($csvContent, $reservoir);
        $measurements = $result['measurements'];
        $errors = $result['errors'];

        // If there are parsing errors and no valid measurements, throw exception
        if (empty($measurements) && !empty($errors)) {
            throw new BadRequestException(
                'No valid measurements found in CSV file. Errors: ' . implode('; ', $errors)
            );
        }

        // Persist measurements
        try {
            foreach ($measurements as $measurement) {
                $this->entityManager->persist($measurement);
            }
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Database error while saving measurements: ' . $e->getMessage());
        }

        // Return result object
        return [
            'success' => true,
            'imported' => count($measurements),
            'skipped' => count($errors),
            'errors' => $errors
        ];
    }
}
