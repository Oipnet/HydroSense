<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Dashboard\DashboardResponse;
use App\State\DashboardProvider;

/**
 * Dashboard API Resource
 * 
 * Provides a synthetic view of the user's farms, reservoirs, measurements, and alerts.
 * This endpoint is used by the frontend dashboard page to display an overview of the farm's status.
 * 
 * Endpoint: GET /api/dashboard
 * 
 * Security: Only accessible by authenticated users (ROLE_USER).
 * Data is automatically scoped to the authenticated user.
 * 
 * Response structure:
 * {
 *   "reservoirs": [
 *     {
 *       "id": 1,
 *       "name": "Bac salade A",
 *       "farmName": "Ferme Nord",
 *       "lastMeasurement": {
 *         "measuredAt": "2025-01-10T08:30:00+00:00",
 *         "ph": 5.9,
 *         "ec": 1.5,
 *         "waterTemp": 20.3
 *       },
 *       "status": "OK"
 *     }
 *   ],
 *   "alerts": {
 *     "total": 3,
 *     "critical": 1,
 *     "warn": 2
 *   }
 * }
 * 
 * Status calculation:
 * - CRITICAL: At least one unresolved CRITICAL alert
 * - WARN: At least one unresolved WARN alert (no CRITICAL)
 * - OK: No unresolved alerts or only INFO alerts
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/dashboard',
            security: "is_granted('ROLE_USER')",
            provider: DashboardProvider::class,
            normalizationContext: ['groups' => ['dashboard:read']],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                summary: 'Get dashboard overview',
                description: 'Returns a synthetic view of the authenticated user\'s farms, reservoirs, latest measurements, and alert statistics. Data is automatically filtered to show only the current user\'s resources.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Dashboard data retrieved successfully',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'reservoirs' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'name' => ['type' => 'string', 'example' => 'Bac salade A'],
                                                    'farmName' => ['type' => 'string', 'example' => 'Ferme Nord'],
                                                    'lastMeasurement' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'measuredAt' => ['type' => 'string', 'format' => 'date-time', 'example' => '2025-01-10T08:30:00+00:00'],
                                                            'ph' => ['type' => 'number', 'format' => 'float', 'example' => 5.9],
                                                            'ec' => ['type' => 'number', 'format' => 'float', 'example' => 1.5],
                                                            'waterTemp' => ['type' => 'number', 'format' => 'float', 'example' => 20.3]
                                                        ],
                                                        'nullable' => true
                                                    ],
                                                    'status' => [
                                                        'type' => 'string',
                                                        'enum' => ['OK', 'WARN', 'CRITICAL'],
                                                        'example' => 'OK',
                                                        'description' => 'Calculated status based on unresolved alerts: CRITICAL if any critical alert, WARN if any warning alert, OK otherwise'
                                                    ]
                                                ]
                                            ]
                                        ],
                                        'alerts' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'total' => ['type' => 'integer', 'example' => 3, 'description' => 'Total number of unresolved alerts'],
                                                'critical' => ['type' => 'integer', 'example' => 1, 'description' => 'Number of unresolved CRITICAL alerts'],
                                                'warn' => ['type' => 'integer', 'example' => 2, 'description' => 'Number of unresolved WARN alerts']
                                            ]
                                        ]
                                    ]
                                ],
                                'example' => [
                                    'reservoirs' => [
                                        [
                                            'id' => 1,
                                            'name' => 'Bac salade A',
                                            'farmName' => 'Ferme Nord',
                                            'lastMeasurement' => [
                                                'measuredAt' => '2025-01-10T08:30:00+00:00',
                                                'ph' => 5.9,
                                                'ec' => 1.5,
                                                'waterTemp' => 20.3
                                            ],
                                            'status' => 'OK'
                                        ],
                                        [
                                            'id' => 2,
                                            'name' => 'Bac tomate B',
                                            'farmName' => 'Ferme Nord',
                                            'lastMeasurement' => [
                                                'measuredAt' => '2025-01-10T09:15:00+00:00',
                                                'ph' => 7.2,
                                                'ec' => 2.8,
                                                'waterTemp' => 22.5
                                            ],
                                            'status' => 'CRITICAL'
                                        ]
                                    ],
                                    'alerts' => [
                                        'total' => 3,
                                        'critical' => 1,
                                        'warn' => 2
                                    ]
                                ]
                            ]
                        ])
                    ),
                    '401' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Unauthorized - User must be authenticated'
                    )
                ]
            )
        )
    ],
    output: DashboardResponse::class
)]
class Dashboard
{
    // This class serves as an API Platform resource definition only.
    // It doesn't need any properties or methods since it's a read-only endpoint
    // powered by DashboardProvider.
}
