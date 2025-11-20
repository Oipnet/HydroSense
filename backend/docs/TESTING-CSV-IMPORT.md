# Guide de Test - Import CSV des Mesures

## Vue d'ensemble

Ce guide explique comment tester l'endpoint d'import CSV pour les mesures des réservoirs.

**Endpoint :** `POST /api/reservoirs/{id}/measurements/import`

## Prérequis

1. Démarrer le serveur Symfony :
```bash
symfony server:start
# ou
php -S localhost:8000 -t public/
```

2. Créer un réservoir de test (si nécessaire) :
```bash
curl -X POST http://localhost:8000/api/reservoirs \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Réservoir Test",
    "description": "Réservoir pour les tests d'\''import CSV",
    "capacity": 1000,
    "location": "Serre A"
  }'
```

Notez l'ID du réservoir retourné (par exemple : `1`).

## Format CSV Attendu

Le fichier CSV doit respecter le format suivant :

- **Séparateur :** point-virgule (`;`)
- **En-tête obligatoire :** `measuredAt;ph;ec;waterTemp`
- **Format de date :** ISO 8601 (exemples : `2024-11-20T10:30:00`, `2024-11-20 10:30:00`, `2024-11-20`)
- **Valeurs numériques :** float ou entier (point ou virgule comme séparateur décimal)
- **Valeurs vides :** acceptées pour ph, ec, waterTemp (au moins une doit être renseignée)

### Exemple de fichier CSV valide :

```csv
measuredAt;ph;ec;waterTemp
2024-11-20T10:30:00;6.5;1.8;22.5
2024-11-20T14:00:00;6.8;1.9;23.0
2024-11-20T18:30:00;6.6;1.85;22.8
2024-11-21T09:00:00;6.7;1.95;23.2
2024-11-21T12:30:00;6.9;2.0;23.5
```

Un fichier d'exemple est disponible dans `backend/examples/measurements_sample.csv`.

## Tests avec curl

### Test 1 : Import CSV valide

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@backend/examples/measurements_sample.csv"
```

**Réponse attendue (200 OK) :**
```json
{
  "success": true,
  "imported": 5,
  "skipped": 0,
  "errors": []
}
```

### Test 2 : Fichier manquant

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import
```

**Réponse attendue (400 Bad Request) :**
```json
{
  "error": "No CSV file provided. Please upload a file with the key \"file\"."
}
```

### Test 3 : Réservoir inexistant

```bash
curl -X POST http://localhost:8000/api/reservoirs/99999/measurements/import \
  -F "file=@backend/examples/measurements_sample.csv"
```

**Réponse attendue (404 Not Found) :**
```json
{
  "error": "Reservoir not found"
}
```

### Test 4 : CSV avec erreurs

Créez un fichier `measurements_invalid.csv` :
```csv
measuredAt;ph;ec;waterTemp
2024-11-20T10:30:00;6.5;1.8;22.5
invalid-date;6.8;1.9;23.0
2024-11-20T18:30:00;invalid;1.85;22.8
```

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@measurements_invalid.csv"
```

**Réponse attendue (200 OK avec erreurs) :**
```json
{
  "success": true,
  "imported": 1,
  "skipped": 2,
  "errors": [
    "Line 3: Invalid date format for measuredAt: \"invalid-date\" (expected ISO 8601 format)",
    "Line 4: Invalid numeric value for ph: \"invalid\""
  ]
}
```

### Test 5 : CSV vide ou mal formaté

Créez un fichier `measurements_empty.csv` vide ou avec un mauvais en-tête :
```csv
date;temperature;conductivity
2024-11-20;22.5;1.8
```

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@measurements_empty.csv"
```

**Réponse attendue (400 Bad Request) :**
```json
{
  "success": false,
  "error": "No valid measurements found in CSV file",
  "errors": [
    "Invalid CSV header. Expected: measuredAt;ph;ec;waterTemp, Got: date;temperature;conductivity"
  ]
}
```

## Tests avec HTTPie

Si vous préférez HTTPie (plus lisible) :

### Installation HTTPie :
```bash
pip install httpie
```

### Import CSV valide :
```bash
http --form POST localhost:8000/api/reservoirs/1/measurements/import \
  file@backend/examples/measurements_sample.csv
```

### Avec authentification JWT (si configurée) :
```bash
http --form POST localhost:8000/api/reservoirs/1/measurements/import \
  "Authorization: Bearer YOUR_JWT_TOKEN" \
  file@backend/examples/measurements_sample.csv
```

## Vérification des données importées

Après un import réussi, vérifiez les mesures créées :

```bash
curl http://localhost:8000/api/measurements?reservoir=/api/reservoirs/1
```

Ou consultez toutes les mesures d'un réservoir :

```bash
curl http://localhost:8000/api/reservoirs/1
```

## Comportement en cas d'erreur

L'endpoint adopte une **stratégie de tolérance aux erreurs** :

- ✅ Les lignes valides sont importées
- ⚠️ Les lignes invalides sont ignorées et reportées dans le tableau `errors`
- ❌ Si aucune ligne n'est valide, l'endpoint retourne une erreur 400

Toutes les mesures importées auront automatiquement :
- `source` = `"CSV_IMPORT"`
- `createdAt` = date/heure de l'import

## Documentation OpenAPI

La documentation OpenAPI/Swagger est accessible à :
```
http://localhost:8000/api/docs
```

Vous y trouverez l'endpoint documenté avec la possibilité de tester directement depuis l'interface.

## Résolution de problèmes

### Erreur "Reservoir not found"
Vérifiez que le réservoir existe en listant tous les réservoirs :
```bash
curl http://localhost:8000/api/reservoirs
```

### Erreur "No CSV file provided"
Assurez-vous d'utiliser le bon nom de champ : `file` (pas `csv`, `upload`, etc.)

### Erreur de format CSV
Vérifiez que :
- Le séparateur est bien `;` (point-virgule)
- L'en-tête est exactement : `measuredAt;ph;ec;waterTemp`
- Les dates sont en format ISO 8601

### Problème d'encodage
Si vous avez des caractères spéciaux, assurez-vous que le fichier CSV est encodé en UTF-8.
