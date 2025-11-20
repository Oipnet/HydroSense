<?php

namespace App\Service;

use App\Entity\Alert;
use App\Entity\CultureProfile;
use App\Entity\Measurement;
use Psr\Log\LoggerInterface;

/**
 * AnomalyDetector service analyzes measurements and detects anomalies
 * by comparing values against CultureProfile thresholds.
 * 
 * This service is called automatically when a Measurement is created
 * to generate appropriate Alert entities.
 * 
 * Detection logic:
 * - pH anomaly: value < phMin OR value > phMax
 * - EC anomaly: value < ecMin OR value > ecMax
 * - Temperature anomaly: value < waterTempMin OR value > waterTempMax
 * 
 * Severity calculation (V1 - simplified):
 * - All anomalies are marked as WARN by default
 * - Future versions will calculate severity based on deviation percentage
 * 
 * @author HydroSense Team
 */
class AnomalyDetector
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Detect anomalies in a measurement based on CultureProfile thresholds.
     * 
     * @param Measurement $measurement The measurement to analyze
     * @param CultureProfile|null $cultureProfile The culture profile with expected ranges
     * @return Alert[] Array of Alert objects to be persisted (not yet persisted)
     */
    public function detect(Measurement $measurement, ?CultureProfile $cultureProfile): array
    {
        // No culture profile = no anomaly detection possible
        if ($cultureProfile === null) {
            $this->logger->info('No CultureProfile configured for measurement analysis', [
                'measurement_id' => $measurement->getId(),
                'reservoir_id' => $measurement->getReservoir()?->getId()
            ]);
            return [];
        }

        $alerts = [];
        $reservoir = $measurement->getReservoir();

        // Check pH anomaly
        if ($measurement->getPh() !== null) {
            $ph = $measurement->getPh();
            $phMin = $cultureProfile->getPhMin();
            $phMax = $cultureProfile->getPhMax();

            if ($ph < $phMin || $ph > $phMax) {
                $alert = $this->createAlert(
                    reservoir: $reservoir,
                    measurement: $measurement,
                    type: Alert::TYPE_PH_OUT_OF_RANGE,
                    measuredValue: $ph,
                    expectedMin: $phMin,
                    expectedMax: $phMax,
                    cultureProfile: $cultureProfile
                );
                $alerts[] = $alert;

                $this->logger->warning('pH anomaly detected', [
                    'measurement_id' => $measurement->getId(),
                    'ph_measured' => $ph,
                    'ph_min' => $phMin,
                    'ph_max' => $phMax
                ]);
            }
        }

        // Check EC anomaly
        if ($measurement->getEc() !== null) {
            $ec = $measurement->getEc();
            $ecMin = $cultureProfile->getEcMin();
            $ecMax = $cultureProfile->getEcMax();

            if ($ec < $ecMin || $ec > $ecMax) {
                $alert = $this->createAlert(
                    reservoir: $reservoir,
                    measurement: $measurement,
                    type: Alert::TYPE_EC_OUT_OF_RANGE,
                    measuredValue: $ec,
                    expectedMin: $ecMin,
                    expectedMax: $ecMax,
                    cultureProfile: $cultureProfile
                );
                $alerts[] = $alert;

                $this->logger->warning('EC anomaly detected', [
                    'measurement_id' => $measurement->getId(),
                    'ec_measured' => $ec,
                    'ec_min' => $ecMin,
                    'ec_max' => $ecMax
                ]);
            }
        }

        // Check water temperature anomaly
        if ($measurement->getWaterTemp() !== null) {
            $temp = $measurement->getWaterTemp();
            $tempMin = $cultureProfile->getWaterTempMin();
            $tempMax = $cultureProfile->getWaterTempMax();

            if ($temp < $tempMin || $temp > $tempMax) {
                $alert = $this->createAlert(
                    reservoir: $reservoir,
                    measurement: $measurement,
                    type: Alert::TYPE_TEMP_OUT_OF_RANGE,
                    measuredValue: $temp,
                    expectedMin: $tempMin,
                    expectedMax: $tempMax,
                    cultureProfile: $cultureProfile
                );
                $alerts[] = $alert;

                $this->logger->warning('Temperature anomaly detected', [
                    'measurement_id' => $measurement->getId(),
                    'temp_measured' => $temp,
                    'temp_min' => $tempMin,
                    'temp_max' => $tempMax
                ]);
            }
        }

        if (count($alerts) > 0) {
            $this->logger->info('Anomaly detection completed', [
                'measurement_id' => $measurement->getId(),
                'alerts_count' => count($alerts)
            ]);
        }

        return $alerts;
    }

    /**
     * Create an Alert object with appropriate severity and message.
     * 
     * @param \App\Entity\Reservoir $reservoir The reservoir where anomaly was detected
     * @param Measurement $measurement The measurement that triggered the alert
     * @param string $type Alert type (PH_OUT_OF_RANGE, EC_OUT_OF_RANGE, TEMP_OUT_OF_RANGE)
     * @param float $measuredValue The measured value
     * @param float $expectedMin The expected minimum value
     * @param float $expectedMax The expected maximum value
     * @param CultureProfile $cultureProfile The culture profile used for comparison
     * @return Alert The created alert (not persisted)
     */
    private function createAlert(
        \App\Entity\Reservoir $reservoir,
        Measurement $measurement,
        string $type,
        float $measuredValue,
        float $expectedMin,
        float $expectedMax,
        CultureProfile $cultureProfile
    ): Alert {
        $alert = new Alert();
        $alert->setReservoir($reservoir);
        $alert->setMeasurement($measurement);
        $alert->setType($type);
        $alert->setMeasuredValue($measuredValue);
        $alert->setExpectedMin($expectedMin);
        $alert->setExpectedMax($expectedMax);

        // Calculate severity based on deviation percentage
        $severity = $this->calculateSeverity($measuredValue, $expectedMin, $expectedMax);
        $alert->setSeverity($severity);

        // Generate human-readable message
        $message = $this->generateMessage($type, $measuredValue, $expectedMin, $expectedMax, $cultureProfile);
        $alert->setMessage($message);

        return $alert;
    }

    /**
     * Calculate severity level based on how far the value is from acceptable range.
     * 
     * V1 Implementation (simplified):
     * - Any deviation is marked as WARN
     * 
     * Future V2 Implementation:
     * - INFO: < 10% outside range
     * - WARN: 10-25% outside range
     * - CRITICAL: > 25% outside range
     * 
     * @param float $value The measured value
     * @param float $min The minimum acceptable value
     * @param float $max The maximum acceptable value
     * @return string Severity level (INFO, WARN, CRITICAL)
     */
    private function calculateSeverity(float $value, float $min, float $max): string
    {
        // Calculate range width
        $rangeWidth = $max - $min;
        
        // Calculate deviation from range
        $deviation = 0;
        if ($value < $min) {
            $deviation = $min - $value;
        } elseif ($value > $max) {
            $deviation = $value - $max;
        }

        // Calculate deviation percentage
        $deviationPercent = ($deviation / $rangeWidth) * 100;

        // Determine severity
        if ($deviationPercent > 25) {
            return Alert::SEVERITY_CRITICAL;
        } elseif ($deviationPercent > 10) {
            return Alert::SEVERITY_WARN;
        }

        return Alert::SEVERITY_INFO;
    }

    /**
     * Generate a human-readable message for the alert.
     * 
     * @param string $type Alert type
     * @param float $measuredValue The measured value
     * @param float $expectedMin The expected minimum
     * @param float $expectedMax The expected maximum
     * @param CultureProfile $cultureProfile The culture profile
     * @return string The formatted message
     */
    private function generateMessage(
        string $type,
        float $measuredValue,
        float $expectedMin,
        float $expectedMax,
        CultureProfile $cultureProfile
    ): string {
        $cultureName = $cultureProfile->getName();

        return match ($type) {
            Alert::TYPE_PH_OUT_OF_RANGE => sprintf(
                'pH level %.2f is outside the recommended range [%.2f - %.2f] for %s',
                $measuredValue,
                $expectedMin,
                $expectedMax,
                $cultureName
            ),
            Alert::TYPE_EC_OUT_OF_RANGE => sprintf(
                'EC level %.2f mS/cm is outside the recommended range [%.2f - %.2f] for %s',
                $measuredValue,
                $expectedMin,
                $expectedMax,
                $cultureName
            ),
            Alert::TYPE_TEMP_OUT_OF_RANGE => sprintf(
                'Water temperature %.2f°C is outside the recommended range [%.2f - %.2f] for %s',
                $measuredValue,
                $expectedMin,
                $expectedMax,
                $cultureName
            ),
            default => 'Unknown anomaly detected'
        };
    }
}
