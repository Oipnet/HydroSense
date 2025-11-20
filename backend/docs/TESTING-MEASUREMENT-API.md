# 🧪 Tests rapides - EPIC-2 Measurement

## Fichiers de test HTTP (REST Client / Postman)

### Configuration

```
@baseUrl = http://localhost:8000
@token = YOUR_JWT_TOKEN_HERE
@reservoirId = 1
```

---

## 1. 🔐 Authentification

```http
### Login
POST {{baseUrl}}/api/login
Content-Type: application/json

{
  "username": "user@example.com",
  "password": "password"
}
```

---

## 2. 📝 Créer des mesures

### POST Standard (avec reservoir explicite)

```http
### Create measurement (standard)
POST {{baseUrl}}/api/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "reservoir": "/api/reservoirs/{{reservoirId}}",
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.5
}
```

### POST Custom (reservoir dans URL)

```http
### Create measurement (custom - reservoir in URL)
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.2,
  "ec": 1.9,
  "waterTemp": 21.8
}
```

### POST avec date spécifique

```http
### Create measurement with specific date
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "measuredAt": "2025-01-15T14:30:00Z",
  "ph": 6.8,
  "ec": 2.0,
  "waterTemp": 23.0
}
```

---

## 3. 📋 Lister les mesures

### Liste complète

```http
### Get all measurements
GET {{baseUrl}}/api/measurements
Authorization: Bearer {{token}}
```

### Filtrer par réservoir

```http
### Get measurements by reservoir
GET {{baseUrl}}/api/measurements?reservoir={{reservoirId}}
Authorization: Bearer {{token}}
```

### Filtrer par date (après)

```http
### Get measurements after date
GET {{baseUrl}}/api/measurements?measuredAt[after]=2025-01-01T00:00:00Z
Authorization: Bearer {{token}}
```

### Filtrer par date (avant)

```http
### Get measurements before date
GET {{baseUrl}}/api/measurements?measuredAt[before]=2025-01-31T23:59:59Z
Authorization: Bearer {{token}}
```

### Filtrer par plage de dates

```http
### Get measurements in date range
GET {{baseUrl}}/api/measurements?measuredAt[after]=2025-01-01T00:00:00Z&measuredAt[before]=2025-01-31T23:59:59Z
Authorization: Bearer {{token}}
```

### Combinaison : réservoir + plage de dates

```http
### Get measurements by reservoir and date range
GET {{baseUrl}}/api/measurements?reservoir={{reservoirId}}&measuredAt[after]=2025-01-01&measuredAt[before]=2025-01-31
Authorization: Bearer {{token}}
```

---

## 4. 🔍 Récupérer une mesure

```http
### Get single measurement
GET {{baseUrl}}/api/measurements/1
Authorization: Bearer {{token}}
```

---

## 5. ✏️ Modifier une mesure

```http
### Update measurement
PUT {{baseUrl}}/api/measurements/1
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.8,
  "ec": 2.0,
  "waterTemp": 23.0
}
```

---

## 6. 🗑️ Supprimer une mesure (Admin)

```http
### Delete measurement (Admin only)
DELETE {{baseUrl}}/api/measurements/1
Authorization: Bearer {{token}}
```

---

## 7. ❌ Tests d'erreur

### Validation pH hors limite

```http
### Invalid pH (out of range)
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 15.0,
  "ec": 1.8,
  "waterTemp": 22.5
}

# Attendu: 422 - "pH must be between 0 and 14"
```

### Validation EC négatif

```http
### Invalid EC (negative)
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.5,
  "ec": -1.0,
  "waterTemp": 22.5
}

# Attendu: 422 - "EC must be a positive value"
```

### Validation température hors limite

```http
### Invalid waterTemp (out of range)
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 60.0
}

# Attendu: 422 - "Water temperature must be between -10°C and 50°C"
```

### Reservoir inexistant

```http
### Reservoir not found
POST {{baseUrl}}/api/reservoirs/9999/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.5
}

# Attendu: 404 - "Reservoir with ID 9999 not found"
```

### Accès à un reservoir d'un autre user

```http
### Access denied (different user's reservoir)
POST {{baseUrl}}/api/reservoirs/999/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.5
}

# Attendu: 403 - "You do not have permission to add measurements to this reservoir"
```

---

## 8. 📊 Scénario complet

### Étape 1 : Créer 3 mesures pour un réservoir

```http
### Morning measurement
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "measuredAt": "2025-01-20T08:00:00Z",
  "ph": 6.3,
  "ec": 1.7,
  "waterTemp": 20.5
}

###

### Noon measurement
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "measuredAt": "2025-01-20T12:00:00Z",
  "ph": 6.5,
  "ec": 1.8,
  "waterTemp": 22.0
}

###

### Evening measurement
POST {{baseUrl}}/api/reservoirs/{{reservoirId}}/measurements
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "measuredAt": "2025-01-20T18:00:00Z",
  "ph": 6.4,
  "ec": 1.75,
  "waterTemp": 21.5
}
```

### Étape 2 : Récupérer les mesures du jour

```http
### Get measurements for the day
GET {{baseUrl}}/api/measurements?reservoir={{reservoirId}}&measuredAt[after]=2025-01-20T00:00:00Z&measuredAt[before]=2025-01-20T23:59:59Z
Authorization: Bearer {{token}}
```

### Étape 3 : Modifier la mesure du midi

```http
### Update noon measurement
PUT {{baseUrl}}/api/measurements/2
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "ph": 6.6,
  "ec": 1.85,
  "waterTemp": 22.5
}
```

### Étape 4 : Vérifier les modifications

```http
### Verify update
GET {{baseUrl}}/api/measurements/2
Authorization: Bearer {{token}}
```

---

## 🔄 Tests avec cURL (alternative)

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"user@example.com","password":"password"}'
```

### Créer une mesure (POST custom)

```bash
TOKEN="YOUR_JWT_TOKEN"
curl -X POST http://localhost:8000/api/reservoirs/1/measurements \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"ph":6.5,"ec":1.8,"waterTemp":22.5}'
```

### Lister les mesures

```bash
curl -X GET http://localhost:8000/api/measurements \
  -H "Authorization: Bearer $TOKEN"
```

### Filtrer par date

```bash
curl -X GET "http://localhost:8000/api/measurements?measuredAt[after]=2025-01-01&measuredAt[before]=2025-01-31" \
  -H "Authorization: Bearer $TOKEN"
```

---

## ✅ Checklist de test

### Tests fonctionnels

-   [ ] POST standard avec reservoir explicite
-   [ ] POST custom avec reservoir dans URL
-   [ ] GET liste complète
-   [ ] GET filtré par reservoir
-   [ ] GET filtré par date (after)
-   [ ] GET filtré par date (before)
-   [ ] GET filtré par plage de dates
-   [ ] GET combiné (reservoir + dates)
-   [ ] GET item spécifique
-   [ ] PUT modification
-   [ ] DELETE (admin)

### Tests de validation

-   [ ] pH hors limite (< 0 ou > 14)
-   [ ] EC négatif
-   [ ] waterTemp hors limite (< -10 ou > 50)
-   [ ] measuredAt invalide (format)
-   [ ] Valeurs manquantes acceptées (nullable)

### Tests de sécurité

-   [ ] Accès refusé sans token
-   [ ] Accès refusé à measurement d'un autre user
-   [ ] Création refusée pour reservoir d'un autre user
-   [ ] Modification refusée pour measurement d'un autre user
-   [ ] Suppression refusée pour non-admin

### Tests automatiques

-   [ ] measuredAt auto-défini à now()
-   [ ] source auto-défini à MANUAL
-   [ ] reservoir auto-associé (POST custom)
-   [ ] createdAt auto-défini

---

## 📝 Notes

-   Remplacer `{{baseUrl}}`, `{{token}}`, `{{reservoirId}}` par vos valeurs
-   Utiliser REST Client (VS Code) ou Postman
-   Les dates doivent être au format ISO 8601 (YYYY-MM-DDTHH:mm:ssZ)
-   Les réponses incluent `@context`, `@id`, `@type` (JSON-LD)

---

## 🎯 Résultats attendus

| Endpoint         | Code HTTP | Réponse                     |
| ---------------- | --------- | --------------------------- |
| POST (succès)    | 201       | Measurement créé avec IRI   |
| GET collection   | 200       | hydra:Collection avec items |
| GET item         | 200       | Measurement complet         |
| PUT              | 200       | Measurement modifié         |
| DELETE           | 204       | Pas de contenu              |
| Validation error | 422       | ConstraintViolationList     |
| Not found        | 404       | hydra:Error                 |
| Access denied    | 403       | hydra:Error                 |
| Unauthorized     | 401       | Error                       |

---

**Bon testing !** 🧪✨
