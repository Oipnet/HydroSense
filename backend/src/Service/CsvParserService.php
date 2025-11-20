<?php

namespace App\Service;

use App\Entity\Measurement;
use App\Entity\Reservoir;

/**
 * Service to parse and validate CSV files for measurement imports.
 * 
 * Expected CSV format:
 * - Separator: semicolon (;)
 * - Header line: measuredAt;ph;ec;waterTemp
 * - Date format: ISO 8601 (YYYY-MM-DDTHH:MM:SS or YYYY-MM-DD HH:MM:SS)
 * - Numeric values: float or integer
 * 
 * Example CSV content:
 * measuredAt;ph;ec;waterTemp
 * 2024-11-20T10:30:00;6.5;1.8;22.5
 * 2024-11-20T14:00:00;6.8;1.9;23.0
 */
class CsvParserService
{
    private const EXPECTED_HEADERS = ['measuredAt', 'ph', 'ec', 'waterTemp'];
    private const CSV_SEPARATOR = ';';

    /**
     * Parse CSV content and create Measurement entities.
     * 
     * @param string $csvContent The CSV file content
     * @param Reservoir $reservoir The reservoir to associate measurements with
     * @return array{measurements: Measurement[], errors: array<int, string>}
     */
    public function parseCsvToMeasurements(string $csvContent, Reservoir $reservoir): array
    {
        $measurements = [];
        $errors = [];
        
        // Split content into lines
        $lines = array_filter(array_map('trim', explode("\n", $csvContent)));
        
        if (empty($lines)) {
            $errors[] = 'CSV file is empty';
            return ['measurements' => $measurements, 'errors' => $errors];
        }

        // Parse header
        $headerLine = array_shift($lines);
        $headers = str_getcsv($headerLine, self::CSV_SEPARATOR);
        
        // Validate header
        if (!$this->validateHeaders($headers)) {
            $errors[] = sprintf(
                'Invalid CSV header. Expected: %s, Got: %s',
                implode(self::CSV_SEPARATOR, self::EXPECTED_HEADERS),
                implode(self::CSV_SEPARATOR, $headers)
            );
            return ['measurements' => $measurements, 'errors' => $errors];
        }

        // Parse data rows
        foreach ($lines as $lineNumber => $line) {
            $actualLineNumber = $lineNumber + 2; // +2 because we removed header and arrays are 0-indexed
            
            if (empty($line)) {
                continue; // Skip empty lines
            }

            $result = $this->parseLine($line, $reservoir, $actualLineNumber);
            
            if ($result['measurement']) {
                $measurements[] = $result['measurement'];
            }
            
            if ($result['error']) {
                $errors[] = $result['error'];
            }
        }

        return ['measurements' => $measurements, 'errors' => $errors];
    }

    /**
     * Validate CSV headers.
     */
    private function validateHeaders(array $headers): bool
    {
        $normalizedHeaders = array_map('trim', $headers);
        return $normalizedHeaders === self::EXPECTED_HEADERS;
    }

    /**
     * Parse a single CSV line into a Measurement entity.
     * 
     * @return array{measurement: ?Measurement, error: ?string}
     */
    private function parseLine(string $line, Reservoir $reservoir, int $lineNumber): array
    {
        $columns = str_getcsv($line, self::CSV_SEPARATOR);
        
        if (count($columns) !== count(self::EXPECTED_HEADERS)) {
            return [
                'measurement' => null,
                'error' => sprintf(
                    'Line %d: Invalid number of columns (expected %d, got %d)',
                    $lineNumber,
                    count(self::EXPECTED_HEADERS),
                    count($columns)
                )
            ];
        }

        [$measuredAtStr, $phStr, $ecStr, $waterTempStr] = array_map('trim', $columns);

        // Validate and parse measuredAt
        $measuredAt = $this->parseDatetime($measuredAtStr);
        if (!$measuredAt) {
            return [
                'measurement' => null,
                'error' => sprintf(
                    'Line %d: Invalid date format for measuredAt: "%s" (expected ISO 8601 format)',
                    $lineNumber,
                    $measuredAtStr
                )
            ];
        }

        // Parse numeric values (allow empty values)
        $ph = $this->parseFloat($phStr);
        $ec = $this->parseFloat($ecStr);
        $waterTemp = $this->parseFloat($waterTempStr);

        // Validate that at least one measurement value is provided
        if ($ph === null && $ec === null && $waterTemp === null) {
            return [
                'measurement' => null,
                'error' => sprintf(
                    'Line %d: At least one measurement value (ph, ec, waterTemp) must be provided',
                    $lineNumber
                )
            ];
        }

        // Validate numeric parsing errors
        if ($phStr !== '' && $ph === null) {
            return [
                'measurement' => null,
                'error' => sprintf('Line %d: Invalid numeric value for ph: "%s"', $lineNumber, $phStr)
            ];
        }
        if ($ecStr !== '' && $ec === null) {
            return [
                'measurement' => null,
                'error' => sprintf('Line %d: Invalid numeric value for ec: "%s"', $lineNumber, $ecStr)
            ];
        }
        if ($waterTempStr !== '' && $waterTemp === null) {
            return [
                'measurement' => null,
                'error' => sprintf('Line %d: Invalid numeric value for waterTemp: "%s"', $lineNumber, $waterTempStr)
            ];
        }

        // Create Measurement entity
        $measurement = new Measurement();
        $measurement->setReservoir($reservoir);
        $measurement->setMeasuredAt($measuredAt);
        $measurement->setPh($ph);
        $measurement->setEc($ec);
        $measurement->setWaterTemp($waterTemp);
        $measurement->setSource(Measurement::SOURCE_CSV_IMPORT);

        return ['measurement' => $measurement, 'error' => null];
    }

    /**
     * Parse a datetime string supporting multiple formats.
     */
    private function parseDatetime(string $dateStr): ?\DateTimeImmutable
    {
        if (empty($dateStr)) {
            return null;
        }

        // Try ISO 8601 formats
        $formats = [
            \DateTimeInterface::ATOM,          // 2024-11-20T10:30:00+00:00
            'Y-m-d\TH:i:s',                    // 2024-11-20T10:30:00
            'Y-m-d H:i:s',                     // 2024-11-20 10:30:00
            'Y-m-d\TH:i:sP',                   // 2024-11-20T10:30:00+01:00
            'Y-m-d',                           // 2024-11-20
        ];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $dateStr);
            if ($date !== false) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Parse a string to float, returning null if empty or invalid.
     */
    private function parseFloat(string $value): ?float
    {
        $value = trim($value);
        
        if ($value === '') {
            return null;
        }

        // Replace comma with dot for decimal separator
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
