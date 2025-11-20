# EPIC-2: Entité Measurement - Guide d'implémentation et de test

## 📋 Résumé de l'implémentation

L'entité `Measurement` a été implémentée avec succès pour gérer les mesures (pH, EC, température) des réservoirs.

### ✅ Fichiers créés/modifiés

1. **Entity/Measurement.php** - Entité avec ApiResource
2. **State/MeasurementPostProcessor.php** - Processor pour les POST
3. **Extension/MeasurementQueryExtension.php** - Sécurité par propriétaire
4. **Repository/MeasurementRepository.php** - Repository Doctrine

### 🔑 Fonctionnalités implémentées

-   ✅ Stockage des mesures (pH, EC, waterTemp, measuredAt, source)
-   ✅ Endpoint standard POST `/api/measurements`
-   ✅ Endpoint custom POST `/api/reservoirs/{id}/measurements`
-   ✅ Filtrage par reservoir et date (from/to)
-   ✅ Sécurité : user == reservoir.farm.owner
-   ✅ Auto-population : measuredAt = now(), source = MANUAL
-   ✅ Relations bidirectionnelles Measurement ↔ Reservoir

---

## 🎯 Architecture technique

### Entité Measurement

```php
Measurement {
    id: int
    reservoir: Reservoir (ManyToOne)
    measuredAt: DateTimeImmutable
    ph: ?float (0-14)
    ec: ?float (> 0)
    waterTemp: ?float (-10 à 50°C)
    source: string (MANUAL | CSV_IMPORT | API_INTEGRATION)
    createdAt: DateTimeImmutable
}
```

### Endpoints disponibles

| Méthode | URL                                 | Description                 | Sécurité                    |
| ------- | ----------------------------------- | --------------------------- | --------------------------- |
| GET     | `/api/measurements`                 | Liste des mesures           | User (filtré par ownership) |
| GET     | `/api/measurements/{id}`            | Détail d'une mesure         | User + ownership check      |
| POST    | `/api/measurements`                 | Créer une mesure            | User + ownership check      |
| POST    | `/api/reservoirs/{id}/measurements` | Créer mesure pour réservoir | User + ownership check      |
| PUT     | `/api/measurements/{id}`            | Modifier une mesure         | User + ownership check      |
| DELETE  | `/api/measurements/{id}`            | Supprimer une mesure        | Admin uniquement            |

### Filtres disponibles

#### Par réservoir

```
GET /api/measurements?reservoir=1
GET /api/measurements?reservoir=/api/reservoirs/1
```

#### Par date

```
GET /api/measurements?measuredAt[after]=2025-01-01T00:00:00Z
GET /api/measurements?measuredAt[before]=2025-01-31T23:59:59Z
GET /api/measurements?measuredAt[after]=2025-01-01&measuredAt[before]=2025-01-31
```

#### Combinaison

```
GET /api/measurements?reservoir=1&measuredAt[after]=2025-01-01&measuredAt[before]=2025-01-31
```

---

## 🧪 Guide de test

### Prérequis

1. Base de données migrée (déjà fait)
2. Utilisateur authentifié avec token JWT
3. Un réservoir existant appartenant à l'utilisateur

### 1. Récupérer un token JWT

```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "username": "user@example.com",
  "password": "password"
}

# Réponse attendue
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### 2. Créer une mesure (POST standard)

```bash
POST http://localhost:8000/api/measurements
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "reservoir": "/api/reservoirs/1",
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.5
}

# Réponse attendue (201 Created)
{
  "@context": "/api/contexts/Measurement",
  "@id": "/api/measurements/1",
  "@type": "Measurement",
  "id": 1,
  "reservoir": "/api/reservoirs/1",
  "measuredAt": "2025-11-20T12:00:00+00:00",
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.5,
  "source": "MANUAL",
  "createdAt": "2025-11-20T12:00:00+00:00"
}
```

#### Comportements automatiques :

-   ✅ `measuredAt` est automatiquement défini à `now()` si non fourni
-   ✅ `source` est automatiquement défini à `"MANUAL"`
-   ✅ Vérifie que l'utilisateur possède le farm du reservoir

### 3. Créer une mesure (POST custom)

```bash
POST http://localhost:8000/api/reservoirs/1/measurements
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "ph": 6.2,
  "ec": 1.9,
  "waterTemp": 21.8,
  "measuredAt": "2025-01-15T14:30:00Z"
}

# Réponse attendue (201 Created)
{
  "@context": "/api/contexts/Measurement",
  "@id": "/api/measurements/2",
  "@type": "Measurement",
  "id": 2,
  "reservoir": "/api/reservoirs/1",
  "measuredAt": "2025-01-15T14:30:00+00:00",
  "ph": 6.2,
  "ec": 1.9,
  "waterTemp": 21.8,
  "source": "MANUAL",
  "createdAt": "2025-11-20T12:00:00+00:00"
}
```

#### Différences avec POST standard :

-   ✅ Pas besoin de spécifier `reservoir` dans le body
-   ✅ Le `reservoir` est automatiquement associé depuis l'ID dans l'URL
-   ✅ Vérifie que l'utilisateur possède le farm du reservoir

### 4. Lister les mesures

```bash
GET http://localhost:8000/api/measurements
Authorization: Bearer {TOKEN}

# Réponse attendue (200 OK)
{
  "@context": "/api/contexts/Measurement",
  "@id": "/api/measurements",
  "@type": "hydra:Collection",
  "hydra:totalItems": 2,
  "hydra:member": [
    {
      "@id": "/api/measurements/1",
      "@type": "Measurement",
      "id": 1,
      "reservoir": "/api/reservoirs/1",
      "measuredAt": "2025-11-20T12:00:00+00:00",
      "ph": 6.5,
      "ec": 1.8,
      "waterTemp": 22.5,
      "source": "MANUAL",
      "createdAt": "2025-11-20T12:00:00+00:00"
    },
    {
      "@id": "/api/measurements/2",
      "@type": "Measurement",
      "id": 2,
      "reservoir": "/api/reservoirs/1",
      "measuredAt": "2025-01-15T14:30:00+00:00",
      "ph": 6.2,
      "ec": 1.9,
      "waterTemp": 21.8,
      "source": "MANUAL",
      "createdAt": "2025-11-20T12:00:00+00:00"
    }
  ]
}
```

#### Notes :

-   ✅ Seules les mesures des réservoirs appartenant à l'utilisateur sont retournées
-   ✅ Fonctionne grâce à `MeasurementQueryExtension`

### 5. Filtrer par réservoir

```bash
GET http://localhost:8000/api/measurements?reservoir=1
Authorization: Bearer {TOKEN}

# Ou avec IRI complet
GET http://localhost:8000/api/measurements?reservoir=/api/reservoirs/1
Authorization: Bearer {TOKEN}
```

### 6. Filtrer par plage de dates

```bash
# Mesures de janvier 2025
GET http://localhost:8000/api/measurements?measuredAt[after]=2025-01-01T00:00:00Z&measuredAt[before]=2025-01-31T23:59:59Z
Authorization: Bearer {TOKEN}

# Mesures après le 15 janvier
GET http://localhost:8000/api/measurements?measuredAt[after]=2025-01-15T00:00:00Z
Authorization: Bearer {TOKEN}

# Mesures avant le 31 janvier
GET http://localhost:8000/api/measurements?measuredAt[before]=2025-01-31T23:59:59Z
Authorization: Bearer {TOKEN}
```

### 7. Combinaison de filtres

```bash
# Mesures d'un réservoir spécifique en janvier 2025
GET http://localhost:8000/api/measurements?reservoir=1&measuredAt[after]=2025-01-01&measuredAt[before]=2025-01-31
Authorization: Bearer {TOKEN}
```

### 8. Récupérer une mesure spécifique

```bash
GET http://localhost:8000/api/measurements/1
Authorization: Bearer {TOKEN}

# Réponse attendue (200 OK)
{
  "@context": "/api/contexts/Measurement",
  "@id": "/api/measurements/1",
  "@type": "Measurement",
  "id": 1,
  "reservoir": "/api/reservoirs/1",
  "measuredAt": "2025-11-20T12:00:00+00:00",
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.5,
  "source": "MANUAL",
  "createdAt": "2025-11-20T12:00:00+00:00"
}
```

### 9. Modifier une mesure

```bash
PUT http://localhost:8000/api/measurements/1
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "ph": 6.8,
  "ec": 2.0,
  "waterTemp": 23.0
}

# Réponse attendue (200 OK)
{
  "@context": "/api/contexts/Measurement",
  "@id": "/api/measurements/1",
  "@type": "Measurement",
  "id": 1,
  "reservoir": "/api/reservoirs/1",
  "measuredAt": "2025-11-20T12:00:00+00:00",
  "ph": 6.8,
  "ec": 2.0,
  "waterTemp": 23.0,
  "source": "MANUAL",
  "createdAt": "2025-11-20T12:00:00+00:00"
}
```

### 10. Supprimer une mesure (Admin uniquement)

```bash
DELETE http://localhost:8000/api/measurements/1
Authorization: Bearer {ADMIN_TOKEN}

# Réponse attendue (204 No Content)
```

---

## 🔒 Sécurité

### Règles implémentées

1. **Lecture (GET)** : L'utilisateur ne peut voir que les mesures des réservoirs de ses propres farms
2. **Création (POST)** : L'utilisateur ne peut créer des mesures que pour ses propres réservoirs
3. **Modification (PUT)** : L'utilisateur ne peut modifier que les mesures de ses réservoirs
4. **Suppression (DELETE)** : Réservé aux administrateurs uniquement

### Tests de sécurité

#### Test 1 : Accès refusé à une mesure d'un autre utilisateur

```bash
GET http://localhost:8000/api/measurements/999
Authorization: Bearer {TOKEN}

# Réponse attendue (404 Not Found)
# La mesure existe mais n'appartient pas à l'utilisateur
```

#### Test 2 : Création refusée pour un réservoir d'un autre utilisateur

```bash
POST http://localhost:8000/api/reservoirs/999/measurements
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "ph": 7.0
}

# Réponse attendue (403 Forbidden ou 404 Not Found)
```

---

## 🎨 Validation des données

### Contraintes implémentées

```php
// pH : entre 0 et 14
"ph": 6.5  // ✅ Valid
"ph": 15.0 // ❌ Invalid: "pH must be between 0 and 14"

// EC : valeur positive
"ec": 1.8  // ✅ Valid
"ec": -1.0 // ❌ Invalid: "EC must be a positive value"

// waterTemp : entre -10°C et 50°C
"waterTemp": 22.5  // ✅ Valid
"waterTemp": 60.0  // ❌ Invalid: "Water temperature must be between -10°C and 50°C"

// reservoir : obligatoire (POST standard uniquement)
"reservoir": "/api/reservoirs/1" // ✅ Valid
// Pas de reservoir                // ❌ Invalid (sauf POST custom)

// source : choix limité (géré automatiquement)
// MANUAL | CSV_IMPORT | API_INTEGRATION
```

### Test de validation

```bash
POST http://localhost:8000/api/measurements
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "reservoir": "/api/reservoirs/1",
  "ph": 15.0,
  "ec": -1.0
}

# Réponse attendue (422 Unprocessable Entity)
{
  "@context": "/api/contexts/ConstraintViolationList",
  "@type": "ConstraintViolationList",
  "hydra:title": "An error occurred",
  "hydra:description": "ph: pH must be between 0 and 14\nec: EC must be a positive value",
  "violations": [
    {
      "propertyPath": "ph",
      "message": "pH must be between 0 and 14"
    },
    {
      "propertyPath": "ec",
      "message": "EC must be a positive value"
    }
  ]
}
```

---

## 📊 Exemples de scénarios réels

### Scénario 1 : Suivi quotidien d'un réservoir

```bash
# Matin (8h00)
POST http://localhost:8000/api/reservoirs/1/measurements
{
  "measuredAt": "2025-01-20T08:00:00Z",
  "ph": 6.3,
  "ec": 1.7,
  "waterTemp": 20.5
}

# Midi (12h00)
POST http://localhost:8000/api/reservoirs/1/measurements
{
  "measuredAt": "2025-01-20T12:00:00Z",
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.0
}

# Soir (18h00)
POST http://localhost:8000/api/reservoirs/1/measurements
{
  "measuredAt": "2025-01-20T18:00:00Z",
  "ph": 6.4,
  "ec": 1.75,
  "waterTemp": 21.5
}

# Récupérer les mesures du jour
GET /api/measurements?reservoir=1&measuredAt[after]=2025-01-20T00:00:00Z&measuredAt[before]=2025-01-20T23:59:59Z
```

### Scénario 2 : Analyse mensuelle

```bash
# Récupérer toutes les mesures de janvier
GET /api/measurements?reservoir=1&measuredAt[after]=2025-01-01&measuredAt[before]=2025-01-31
```

### Scénario 3 : Surveillance multi-réservoirs

```bash
# Réservoir 1
GET /api/measurements?reservoir=1&measuredAt[after]=2025-01-20

# Réservoir 2
GET /api/measurements?reservoir=2&measuredAt[after]=2025-01-20
```

---

## 🚀 Commandes utiles

### Voir les routes API

```bash
php bin/console debug:router | grep measurement
```

### Vider le cache

```bash
php bin/console cache:clear
```

### Vérifier la configuration API Platform

```bash
php bin/console debug:config api_platform
```

### Voir la structure de la base

```bash
php bin/console doctrine:schema:validate
```

---

## ✅ Acceptance Criteria - Validation

-   [x] `GET /api/measurements?reservoir=ID` retourne les mesures du réservoir
-   [x] `POST /api/reservoirs/{id}/measurements` crée une mesure pour ce réservoir
-   [x] Le filtrage par date fonctionne (`measuredAt[after]`, `measuredAt[before]`)
-   [x] La ressource est sécurisée : un user ne peut manipuler que les mesures liées à ses propres réservoirs/farms
-   [x] `measuredAt` est automatiquement défini à `now()` si absent
-   [x] `source` est automatiquement défini à `"MANUAL"` pour les créations manuelles

---

## 📝 Notes pour les prochaines EPIC

### EPIC-5 : Import CSV de mesures

-   Le champ `source` peut être défini à `"CSV_IMPORT"` lors de l'import
-   Utiliser le `CsvImportProcessor` existant ou créer un spécifique

### Intégration API externe

-   Le champ `source` peut être défini à `"API_INTEGRATION"`
-   Prévoir un processor dédié pour les imports automatiques

### Analytics & Reporting

-   Les mesures sont déjà filtrables par date
-   Facile d'ajouter des agrégations (moyenne, min, max)
-   Possibilité d'ajouter des endpoints custom pour des statistiques

---

## 🐛 Troubleshooting

### Erreur 403 lors de la création

**Problème** : `Access denied - user does not own the reservoir's farm`
**Solution** : Vérifier que le reservoir appartient bien à une farm de l'utilisateur connecté

### Erreur 404 sur POST custom

**Problème** : `Reservoir not found`
**Solution** : Vérifier que le reservoir ID existe et appartient à l'utilisateur

### Aucune mesure retournée

**Problème** : `hydra:totalItems: 0`
**Solution** : Vérifier que :

1. Des mesures existent pour vos réservoirs
2. Vous êtes bien authentifié
3. Le filtre `reservoir` pointe vers un réservoir qui vous appartient

### Erreur de validation

**Problème** : `422 Unprocessable Entity`
**Solution** : Vérifier les contraintes :

-   pH entre 0 et 14
-   EC positif
-   waterTemp entre -10 et 50
-   reservoir obligatoire (POST standard)

---

## 📚 Documentation API

### OpenAPI / Swagger

L'API est automatiquement documentée via API Platform.

Accéder à la documentation :

```
http://localhost:8000/api/docs
```

La documentation inclut :

-   Tous les endpoints
-   Les schémas de données
-   Les exemples de requêtes/réponses
-   Les règles de sécurité

---

## 🎉 Conclusion

L'implémentation de l'entité `Measurement` est **complète et opérationnelle**.

Toutes les fonctionnalités requises ont été implémentées :

-   ✅ Endpoints CRUD complets
-   ✅ POST custom par réservoir
-   ✅ Filtrage par date et réservoir
-   ✅ Sécurité stricte par propriétaire
-   ✅ Validation des données
-   ✅ Auto-population des champs
-   ✅ Documentation inline pour IA

**Prêt pour la production et les tests !** 🚀
