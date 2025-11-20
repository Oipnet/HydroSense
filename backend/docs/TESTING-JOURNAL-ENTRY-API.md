# Guide de test pour JournalEntry API

Ce fichier contient des exemples de requêtes pour tester l'API JournalEntry.

## Configuration

Avant de commencer, définissez ces variables dans PowerShell :

```powershell
# Configuration de base
$API_URL = "http://localhost:8000"

# Tokens d'authentification (à remplacer par vos vrais tokens)
$TOKEN_USER_A = "votre_token_jwt_user_a"
$TOKEN_USER_B = "votre_token_jwt_user_b"

# IDs de ressources (à adapter selon votre base)
$RESERVOIR_ID_USER_A = "1"
$RESERVOIR_ID_USER_B = "2"
```

## 1. Créer une entrée de journal (User A)

```powershell
# Avec photo
$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID_USER_A"
    content = "Premier test du journal de culture. pH ajusté à 6.5 après ajout de nutriments."
    photoUrl = "https://example.com/photos/reservoir-20250120.jpg"
} | ConvertTo-Json

Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
    -Method Post `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
        "Content-Type" = "application/json"
    } `
    -Body $body
```

```powershell
# Sans photo
$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID_USER_A"
    content = "Observation quotidienne : les plantes poussent bien, température stable."
} | ConvertTo-Json

Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
    -Method Post `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
        "Content-Type" = "application/json"
    } `
    -Body $body
```

## 2. Lister toutes les entrées (User A)

```powershell
# Liste complète
Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
    -Method Get `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
    }
```

## 3. Récupérer une entrée spécifique (User A)

```powershell
$ENTRY_ID = "1"

Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
    -Method Get `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
    }
```

## 4. Mettre à jour une entrée (User A)

```powershell
$ENTRY_ID = "1"

$body = @{
    content = "Contenu modifié : pH stable à 6.5, EC légèrement augmenté."
    photoUrl = $null
} | ConvertTo-Json

Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
    -Method Put `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
        "Content-Type" = "application/json"
    } `
    -Body $body
```

## 5. Tests de sécurité

### Test 1 : User B tente de lire l'entrée de User A (doit échouer)

```powershell
$ENTRY_ID_USER_A = "1"

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID_USER_A" `
        -Method Get `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_B"
        }
    Write-Host "❌ ERREUR : L'accès aurait dû être refusé" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Accès refusé comme attendu (403 ou 404)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

### Test 2 : User B tente de créer une entrée pour le réservoir de User A (doit échouer)

```powershell
$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID_USER_A"
    content = "Tentative d'écriture non autorisée"
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
        -Method Post `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_B"
            "Content-Type" = "application/json"
        } `
        -Body $body
    Write-Host "❌ ERREUR : La création aurait dû être refusée" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Création refusée comme attendu (403)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

### Test 3 : User B tente de modifier l'entrée de User A (doit échouer)

```powershell
$ENTRY_ID_USER_A = "1"

$body = @{
    content = "Tentative de modification non autorisée"
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID_USER_A" `
        -Method Put `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_B"
            "Content-Type" = "application/json"
        } `
        -Body $body
    Write-Host "❌ ERREUR : La modification aurait dû être refusée" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Modification refusée comme attendu (403 ou 404)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

### Test 4 : User B tente de supprimer l'entrée de User A (doit échouer)

```powershell
$ENTRY_ID_USER_A = "1"

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID_USER_A" `
        -Method Delete `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_B"
        }
    Write-Host "❌ ERREUR : La suppression aurait dû être refusée" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Suppression refusée comme attendu (403 ou 404)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

## 6. Tests de validation

### Test 1 : Créer une entrée sans contenu (doit échouer)

```powershell
$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID_USER_A"
    content = ""
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
        -Method Post `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
            "Content-Type" = "application/json"
        } `
        -Body $body
    Write-Host "❌ ERREUR : La validation aurait dû échouer" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Validation échouée comme attendu (422)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

### Test 2 : Créer une entrée sans réservoir (doit échouer)

```powershell
$body = @{
    content = "Test sans réservoir"
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
        -Method Post `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
            "Content-Type" = "application/json"
        } `
        -Body $body
    Write-Host "❌ ERREUR : La validation aurait dû échouer" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Validation échouée comme attendu (422)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

### Test 3 : Créer une entrée avec un contenu trop long (doit échouer)

```powershell
$longContent = "a" * 5001  # Plus de 5000 caractères

$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID_USER_A"
    content = $longContent
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
        -Method Post `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
            "Content-Type" = "application/json"
        } `
        -Body $body
    Write-Host "❌ ERREUR : La validation aurait dû échouer" -ForegroundColor Red
} catch {
    Write-Host "✅ SUCCÈS : Validation échouée comme attendu (422)" -ForegroundColor Green
    Write-Host $_.Exception.Message
}
```

## 7. Supprimer une entrée (User A)

```powershell
$ENTRY_ID = "1"

Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
    -Method Delete `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
    }

Write-Host "✅ Entrée supprimée avec succès" -ForegroundColor Green
```

## 8. Consulter les entrées d'un réservoir spécifique

```powershell
# Via le détail du réservoir (inclut les entrées de journal)
Invoke-RestMethod -Uri "$API_URL/api/reservoirs/$RESERVOIR_ID_USER_A" `
    -Method Get `
    -Headers @{
        "Authorization" = "Bearer $TOKEN_USER_A"
    }
```

## Script de test complet automatisé

```powershell
# Configuration
$API_URL = "http://localhost:8000"
$TOKEN_USER_A = "votre_token_jwt_user_a"
$TOKEN_USER_B = "votre_token_jwt_user_b"
$RESERVOIR_ID_USER_A = "1"

Write-Host "=== Test de l'API JournalEntry ===" -ForegroundColor Cyan

# Test 1: Créer une entrée (User A)
Write-Host "`n[Test 1] Création d'une entrée de journal..." -ForegroundColor Yellow
$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID_USER_A"
    content = "Test automatisé: première entrée"
    photoUrl = "https://example.com/test.jpg"
} | ConvertTo-Json

try {
    $entry = Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
        -Method Post `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
            "Content-Type" = "application/json"
        } `
        -Body $body

    $ENTRY_ID = $entry.id
    Write-Host "✅ Entrée créée avec ID: $ENTRY_ID" -ForegroundColor Green
} catch {
    Write-Host "❌ Échec: $($_.Exception.Message)" -ForegroundColor Red
    exit
}

# Test 2: Lire l'entrée (User A)
Write-Host "`n[Test 2] Lecture de l'entrée..." -ForegroundColor Yellow
try {
    $entry = Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
        -Method Get `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
        }
    Write-Host "✅ Entrée lue avec succès" -ForegroundColor Green
} catch {
    Write-Host "❌ Échec: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Tentative de lecture (User B) - doit échouer
Write-Host "`n[Test 3] Tentative de lecture par User B..." -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
        -Method Get `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_B"
        }
    Write-Host "❌ L'accès aurait dû être refusé" -ForegroundColor Red
} catch {
    Write-Host "✅ Accès refusé comme attendu" -ForegroundColor Green
}

# Test 4: Mettre à jour l'entrée (User A)
Write-Host "`n[Test 4] Mise à jour de l'entrée..." -ForegroundColor Yellow
$body = @{
    content = "Contenu mis à jour"
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
        -Method Put `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
            "Content-Type" = "application/json"
        } `
        -Body $body
    Write-Host "✅ Entrée mise à jour avec succès" -ForegroundColor Green
} catch {
    Write-Host "❌ Échec: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Supprimer l'entrée (User A)
Write-Host "`n[Test 5] Suppression de l'entrée..." -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "$API_URL/api/journal_entries/$ENTRY_ID" `
        -Method Delete `
        -Headers @{
            "Authorization" = "Bearer $TOKEN_USER_A"
        }
    Write-Host "✅ Entrée supprimée avec succès" -ForegroundColor Green
} catch {
    Write-Host "❌ Échec: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== Tests terminés ===" -ForegroundColor Cyan
```

## Aide-mémoire des endpoints

| Méthode | Endpoint                    | Description                               | Auth requise |
| ------- | --------------------------- | ----------------------------------------- | ------------ |
| GET     | `/api/journal_entries`      | Liste toutes les entrées de l'utilisateur | ✅           |
| GET     | `/api/journal_entries/{id}` | Récupère une entrée spécifique            | ✅           |
| POST    | `/api/journal_entries`      | Crée une nouvelle entrée                  | ✅           |
| PUT     | `/api/journal_entries/{id}` | Met à jour une entrée                     | ✅           |
| DELETE  | `/api/journal_entries/{id}` | Supprime une entrée                       | ✅           |

## Format des données

### Requête POST/PUT

```json
{
    "reservoir": "/api/reservoirs/{id}",
    "content": "Votre contenu ici...",
    "photoUrl": "https://example.com/photo.jpg" // optionnel
}
```

### Réponse GET

```json
{
    "@context": "/api/contexts/JournalEntry",
    "@id": "/api/journal_entries/1",
    "@type": "JournalEntry",
    "id": 1,
    "reservoir": "/api/reservoirs/1",
    "content": "Votre contenu ici...",
    "photoUrl": "https://example.com/photo.jpg",
    "createdAt": "2025-11-20T10:30:00+00:00",
    "updatedAt": "2025-11-20T10:30:00+00:00"
}
```
