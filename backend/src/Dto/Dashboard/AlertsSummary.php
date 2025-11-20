<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * AlertsSummary provides aggregated counts of alerts for the dashboard.
 * Shows total unresolved alerts broken down by severity.
 */
class AlertsSummary
{
    #[Groups(['dashboard:read'])]
    public int $total = 0;

    #[Groups(['dashboard:read'])]
    public int $critical = 0;

    #[Groups(['dashboard:read'])]
    public int $warn = 0;

    public function __construct(int $total = 0, int $critical = 0, int $warn = 0)
    {
        $this->total = $total;
        $this->critical = $critical;
        $this->warn = $warn;
    }
}
