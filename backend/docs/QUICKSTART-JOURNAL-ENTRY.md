# 🚀 Guide de démarrage rapide - JournalEntry

## Configuration (5 minutes)

### 1. Vérifier que la migration est appliquée

```powershell
php bin/console doctrine:migrations:status
```

Si la migration `Version20251120115107` n'est pas appliquée :

```powershell
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Lancer le serveur

```powershell
symfony serve
# ou
php -S localhost:8000 -t public
```

### 3. Créer un utilisateur de test (si nécessaire)

```powershell
php bin/console app:create-user test@example.com password "Test User"
```

## Test rapide (2 minutes)

### 1. Obtenir un token JWT

**PowerShell** :

```powershell
$login = @{
    username = "test@example.com"
    password = "password"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/login_check" `
    -Method Post `
    -ContentType "application/json" `
    -Body $login

$TOKEN = $response.token
Write-Host "Token obtenu : $TOKEN"
```

### 2. Créer une entrée de journal

**PowerShell** :

```powershell
$entry = @{
    reservoir = "/api/reservoirs/1"
    content = "Test de l'API JournalEntry"
    photoUrl = "https://example.com/test.jpg"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/journal_entries" `
    -Method Post `
    -Headers @{
        "Authorization" = "Bearer $TOKEN"
        "Content-Type" = "application/json"
    } `
    -Body $entry
```

### 3. Lister les entrées

**PowerShell** :

```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/journal_entries" `
    -Method Get `
    -Headers @{ "Authorization" = "Bearer $TOKEN" }
```

## Résolution de problèmes

### Erreur : "Table journal_entry doesn't exist"

**Solution** : Exécutez la migration

```powershell
php bin/console doctrine:migrations:migrate
```

### Erreur : "Access Denied" ou 403

**Solution** : Vérifiez que :

1. Le token JWT est valide
2. L'utilisateur possède bien le réservoir
3. Le réservoir existe

### Erreur : "Reservoir not found" ou 404

**Solution** : Créez d'abord un réservoir

```powershell
$reservoir = @{
    name = "Réservoir de test"
    farm = "/api/farms/1"
    volumeLiters = 100
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/reservoirs" `
    -Method Post `
    -Headers @{
        "Authorization" = "Bearer $TOKEN"
        "Content-Type" = "application/json"
    } `
    -Body $reservoir
```

### Erreur : "Content cannot be blank"

**Solution** : Assurez-vous que le champ `content` n'est pas vide

### Cache issues

**Solution** : Videz le cache

```powershell
php bin/console cache:clear
```

## Documentation complète

Pour plus de détails, consultez :

-   **Vue d'ensemble** : `docs/README-JOURNAL-ENTRY.md`
-   **Documentation technique** : `docs/EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md`
-   **Guide de test complet** : `docs/TESTING-JOURNAL-ENTRY-API.md`
-   **Exemples de données** : `examples/journal_entries_examples.md`
-   **Synthèse** : `SYNTHESE-JOURNAL-ENTRY.md`

## Commandes utiles

```powershell
# Vider le cache
php bin/console cache:clear

# Voir les routes JournalEntry
php bin/console debug:router | Select-String "journal"

# Valider le schéma
php bin/console doctrine:schema:validate

# Voir les migrations
php bin/console doctrine:migrations:list

# Voir les logs
Get-Content var/log/dev.log -Tail 50 -Wait
```

## Checklist avant de commencer

-   [ ] Serveur Symfony lancé
-   [ ] Migration appliquée
-   [ ] Au moins 1 utilisateur créé
-   [ ] Au moins 1 ferme créée
-   [ ] Au moins 1 réservoir créé
-   [ ] Token JWT obtenu

## Prêt à développer ! 🎉

L'API JournalEntry est maintenant opérationnelle. Vous pouvez :

-   Créer des entrées de journal
-   Lister les entrées
-   Mettre à jour les entrées
-   Supprimer les entrées

Toutes les opérations sont sécurisées et filtrent automatiquement par propriétaire.
