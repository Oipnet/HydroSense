<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DashboardResponse is the main DTO returned by the /api/dashboard endpoint.
 * Provides a comprehensive view of the user's farms, reservoirs, measurements, and alerts.
 */
class DashboardResponse
{
    /**
     * @var ReservoirSummary[]
     */
    #[Groups(['dashboard:read'])]
    public array $reservoirs = [];

    #[Groups(['dashboard:read'])]
    public AlertsSummary $alerts;

    /**
     * @param ReservoirSummary[] $reservoirs
     */
    public function __construct(array $reservoirs = [], ?AlertsSummary $alerts = null)
    {
        $this->reservoirs = $reservoirs;
        $this->alerts = $alerts ?? new AlertsSummary();
    }
}
