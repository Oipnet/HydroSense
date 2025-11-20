<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * LastMeasurementView represents the most recent measurement data for a reservoir.
 * Used in the dashboard to display current status.
 */
class LastMeasurementView
{
    #[Groups(['dashboard:read'])]
    public ?\DateTimeImmutable $measuredAt = null;

    #[Groups(['dashboard:read'])]
    public ?float $ph = null;

    #[Groups(['dashboard:read'])]
    public ?float $ec = null;

    #[Groups(['dashboard:read'])]
    public ?float $waterTemp = null;

    public function __construct(
        ?\DateTimeImmutable $measuredAt = null,
        ?float $ph = null,
        ?float $ec = null,
        ?float $waterTemp = null
    ) {
        $this->measuredAt = $measuredAt;
        $this->ph = $ph;
        $this->ec = $ec;
        $this->waterTemp = $waterTemp;
    }
}
