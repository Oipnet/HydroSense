<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * ReservoirSummary provides a summary of a reservoir's current state.
 * Includes basic info, last measurement, and calculated status based on alerts.
 */
class ReservoirSummary
{
    #[Groups(['dashboard:read'])]
    public ?int $id = null;

    #[Groups(['dashboard:read'])]
    public ?string $name = null;

    #[Groups(['dashboard:read'])]
    public ?string $farmName = null;

    #[Groups(['dashboard:read'])]
    public ?LastMeasurementView $lastMeasurement = null;

    /**
     * Status: "OK", "WARN", or "CRITICAL"
     * Calculated based on unresolved alerts for this reservoir.
     */
    #[Groups(['dashboard:read'])]
    public string $status = 'OK';

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $farmName = null,
        ?LastMeasurementView $lastMeasurement = null,
        string $status = 'OK'
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->farmName = $farmName;
        $this->lastMeasurement = $lastMeasurement;
        $this->status = $status;
    }
}
