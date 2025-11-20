# Dashboard DTOs

Ce dossier contient les Data Transfer Objects (DTOs) utilisés par l'endpoint `/api/dashboard`.

## Structure des DTOs

```
Dashboard/
├── DashboardResponse.php       # DTO principal de réponse
├── ReservoirSummary.php        # Résumé d'un réservoir
├── LastMeasurementView.php     # Vue de la dernière mesure
└── AlertsSummary.php           # Résumé des alertes
```

## Hiérarchie des DTOs

```
DashboardResponse
├── reservoirs: ReservoirSummary[]
│   ├── id: int
│   ├── name: string
│   ├── farmName: string
│   ├── lastMeasurement: LastMeasurementView|null
│   │   ├── measuredAt: DateTimeImmutable
│   │   ├── ph: float
│   │   ├── ec: float
│   │   └── waterTemp: float
│   └── status: string (OK|WARN|CRITICAL)
└── alerts: AlertsSummary
    ├── total: int
    ├── critical: int
    └── warn: int
```

## Exemple JSON

```json
{
  "reservoirs": [
    {
      "id": 1,
      "name": "Bac salade A",
      "farmName": "Ferme Nord",
      "lastMeasurement": {
        "measuredAt": "2025-01-10T08:30:00+00:00",
        "ph": 5.9,
        "ec": 1.5,
        "waterTemp": 20.3
      },
      "status": "OK"
    }
  ],
  "alerts": {
    "total": 3,
    "critical": 1,
    "warn": 2
  }
}
```

## Groupes de sérialisation

Tous les DTOs utilisent le groupe `dashboard:read` pour la normalisation JSON.

## Utilisation

Ces DTOs sont utilisés exclusivement par :
- **Provider** : `App\State\DashboardProvider`
- **Ressource** : `App\ApiResource\Dashboard`
- **Endpoint** : `GET /api/dashboard`

## Documentation

Voir la documentation complète dans :
- `docs/EPIC-2-DASHBOARD-IMPLEMENTATION.md`
- `docs/TESTING-DASHBOARD-API.md`
