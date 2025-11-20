# 📊 Plan d'Implémentation - Dashboard Endpoint

## Issue #13 - [EPIC-2] Endpoint Dashboard (vue synthèse backend)

---

## 📋 Plan détaillé d'implémentation

### Étape 1 : DTOs (Data Transfer Objects) ✅

**Objectif** : Créer les structures de données pour la réponse JSON

**Fichiers créés** :

1. **`src/Dto/Dashboard/LastMeasurementView.php`**
   - Représente la dernière mesure d'un réservoir
   - Propriétés : `measuredAt`, `ph`, `ec`, `waterTemp`
   - Groupe de sérialisation : `dashboard:read`

2. **`src/Dto/Dashboard/ReservoirSummary.php`**
   - Résumé d'un réservoir avec son statut
   - Propriétés : `id`, `name`, `farmName`, `lastMeasurement`, `status`
   - Statut : `"OK"`, `"WARN"`, ou `"CRITICAL"`

3. **`src/Dto/Dashboard/AlertsSummary.php`**
   - Agrégation des compteurs d'alertes
   - Propriétés : `total`, `critical`, `warn`

4. **`src/Dto/Dashboard/DashboardResponse.php`**
   - DTO principal de réponse
   - Propriétés : `reservoirs[]` (ReservoirSummary), `alerts` (AlertsSummary)

---

### Étape 2 : Provider Custom ✅

**Objectif** : Implémenter la logique métier pour récupérer et calculer les données

**Fichier créé** : `src/State/DashboardProvider.php`

**Implémente** : `ProviderInterface` d'API Platform

**Logique** :

1. **Récupération de l'utilisateur authentifié**
   ```php
   $user = $this->security->getUser();
   ```

2. **Chargement des réservoirs de l'utilisateur**
   - Requête Doctrine avec `JOIN` sur `farm.owner`
   - Filtre automatique par utilisateur connecté

3. **Récupération des alertes non résolues**
   ```php
   $unresolvedAlerts = $this->alertRepository->findUnresolvedForUser($user);
   ```

4. **Pour chaque réservoir** :
   - Récupère la dernière mesure (ORDER BY measuredAt DESC, LIMIT 1)
   - Calcule le statut basé sur les alertes :
     - CRITICAL si au moins 1 alerte CRITICAL
     - WARN si au moins 1 alerte WARN (sans CRITICAL)
     - OK sinon

5. **Agrégation des alertes**
   - Compte total, critical, warn

6. **Construction de la réponse**
   - Retourne un objet `DashboardResponse`

---

### Étape 3 : Ressource API Platform ✅

**Objectif** : Exposer l'endpoint `/api/dashboard` avec API Platform

**Fichier créé** : `src/ApiResource/Dashboard.php`

**Configuration** :

- **URI** : `/api/dashboard`
- **Méthode** : `GET` uniquement
- **Sécurité** : `is_granted('ROLE_USER')`
- **Provider** : `DashboardProvider::class`
- **Normalisation** : Groupe `dashboard:read`
- **Output** : `DashboardResponse::class`

**Documentation OpenAPI** :

- Summary : "Get dashboard overview"
- Description complète de l'endpoint
- Schéma de réponse détaillé avec exemples
- Codes d'erreur : 200 (OK), 401 (Unauthorized)

---

### Étape 4 : Documentation ✅

**Fichiers créés** :

1. **`docs/EPIC-2-DASHBOARD-IMPLEMENTATION.md`**
   - Documentation complète de l'implémentation
   - Logique métier détaillée
   - Cas d'usage et exemples
   - Guide de debugging

2. **`docs/TESTING-DASHBOARD-API.md`**
   - Guide de test rapide avec curl
   - Scénarios de test (user avec/sans données, isolation)
   - Checklist de vérification
   - Dépannage

3. **`docs/ISSUE-13-COMPLETE.md`**
   - Récapitulatif complet de l'implémentation
   - Liste des fichiers créés
   - Architecture du système
   - Checklist finale

---

## 🎯 Code complet des fichiers créés

### 1. LastMeasurementView.php

```php
<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

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
```

### 2. ReservoirSummary.php

```php
<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

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
```

### 3. AlertsSummary.php

```php
<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

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
```

### 4. DashboardResponse.php

```php
<?php

namespace App\Dto\Dashboard;

use Symfony\Component\Serializer\Annotation\Groups;

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
```

### 5. DashboardProvider.php

```php
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

class DashboardProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ReservoirRepository $reservoirRepository,
        private readonly MeasurementRepository $measurementRepository,
        private readonly AlertRepository $alertRepository,
    ) {
    }

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

    private function calculateReservoirStatus(array $alerts): string
    {
        if (empty($alerts)) {
            return 'OK';
        }

        foreach ($alerts as $alert) {
            if ($alert->getSeverity() === Alert::SEVERITY_CRITICAL) {
                return 'CRITICAL';
            }
        }

        foreach ($alerts as $alert) {
            if ($alert->getSeverity() === Alert::SEVERITY_WARN) {
                return 'WARN';
            }
        }

        return 'OK';
    }

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
```

### 6. Dashboard.php (ApiResource)

```php
<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Dashboard\DashboardResponse;
use App\State\DashboardProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/dashboard',
            security: "is_granted('ROLE_USER')",
            provider: DashboardProvider::class,
            normalizationContext: ['groups' => ['dashboard:read']],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                summary: 'Get dashboard overview',
                description: 'Returns a synthetic view of the authenticated user\'s farms, reservoirs, latest measurements, and alert statistics.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Dashboard data retrieved successfully'
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
    // Read-only resource - no properties needed
}
```

---

## 🧪 Guide de test rapide

### 1. Vérifier que la route est enregistrée

```bash
cd backend
php bin/console debug:router | grep dashboard
```

**Résultat attendu** :
```
_api_/dashboard_get    GET    ANY    ANY    /api/dashboard
```

### 2. Tester l'endpoint (avec authentification)

```bash
# S'authentifier
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# Appeler le dashboard
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

### 3. Réponse attendue

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

---

## 🎉 Résultat

✅ **Implémentation complète** de l'endpoint `/api/dashboard`  
✅ **7 fichiers créés** (4 DTOs + 1 Provider + 1 ApiResource + 3 docs)  
✅ **Aucune erreur** de compilation/linting  
✅ **Route enregistrée** : `GET /api/dashboard`  
✅ **Sécurité** : `ROLE_USER` requis  
✅ **Documentation** : OpenAPI + guides complets  
✅ **Tests** : Guide de test avec curl fourni  

L'endpoint est **prêt à être utilisé** ! 🚀

---

## 📚 Documentation complète

- [EPIC-2-DASHBOARD-IMPLEMENTATION.md](./docs/EPIC-2-DASHBOARD-IMPLEMENTATION.md) - Documentation technique complète
- [TESTING-DASHBOARD-API.md](./docs/TESTING-DASHBOARD-API.md) - Guide de test avec curl
- [ISSUE-13-COMPLETE.md](./docs/ISSUE-13-COMPLETE.md) - Récapitulatif de l'issue
- [OpenAPI Docs](http://localhost:8000/api/docs) - Documentation API interactive

---

**Issue #13 : FERMÉE** ✅
