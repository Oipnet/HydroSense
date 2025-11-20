# Exemples de données pour JournalEntry

Ce fichier contient des exemples de contenu pour tester l'API JournalEntry.

## Exemples de contenu court

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "pH stable à 6.5"
}
```

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Ajout de 50ml de solution nutritive A"
}
```

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Température de l'eau : 22°C"
}
```

## Exemples de contenu détaillé

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Observation quotidienne :\n- pH : 6.5\n- EC : 1.8 mS/cm\n- Température eau : 21°C\n- Niveau eau : 75%\n- État général : bon\n\nActions effectuées :\n- Ajout de 100ml solution nutritive A\n- Ajout de 50ml solution nutritive B\n- Nettoyage du filtre",
    "photoUrl": "https://example.com/photos/reservoir-20250120-morning.jpg"
}
```

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Changement de solution complète aujourd'hui.\n\nAnciens paramètres :\n- pH : 7.2 (trop élevé)\n- EC : 2.4 mS/cm (trop concentré)\n- Volume restant : ~30L\n\nNouveaux paramètres :\n- pH : 6.3 (ajusté)\n- EC : 1.6 mS/cm\n- Volume : 100L (réservoir plein)\n\nProchaine vérification : dans 2 jours",
    "photoUrl": "https://example.com/photos/solution-change-20250120.jpg"
}
```

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "⚠️ ALERTE : pH trop bas détecté ce matin.\n\nMesure initiale : pH 5.2\nAction : Ajout de pH+ (solution de potasse)\nMesure après 30min : pH 6.4\nMesure après 2h : pH 6.5\n\n✅ Situation normalisée.\n\nNote : Vérifier la consommation de nutriments, les plantes semblent croître rapidement.",
    "photoUrl": null
}
```

## Exemples avec différentes activités

### Entretien

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Maintenance hebdomadaire :\n✓ Nettoyage des parois du réservoir\n✓ Vérification des tuyaux\n✓ Nettoyage des filtres\n✓ Test de la pompe à air\n✓ Calibration du pH-mètre\n\nTout fonctionne correctement.",
    "photoUrl": "https://example.com/photos/maintenance-20250120.jpg"
}
```

### Problème résolu

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Problème : Pompe à air défaillante détectée hier soir.\n\nSymptômes :\n- Diminution de l'oxygénation\n- Racines légèrement brunes\n\nSolution :\n- Remplacement de la pompe à air\n- Ajout de peroxyde d'hydrogène (H2O2) : 5ml/L\n- Surveillance accrue pendant 48h\n\nRésultat après 24h :\n- Oxygénation normale\n- Racines retrouvent une couleur saine",
    "photoUrl": "https://example.com/photos/pump-replacement-20250120.jpg"
}
```

### Observation de croissance

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Semaine 3 de croissance végétative :\n\n🌱 Observations :\n- Hauteur moyenne : 25cm\n- Nouvelles feuilles : 4-5 par plant\n- Couleur : vert foncé intense\n- Système racinaire : très développé\n\n📊 Paramètres :\n- pH : 6.2\n- EC : 1.9 mS/cm\n- Température : 22°C\n- Humidité ambiante : 65%\n\n📝 Notes :\n- Augmentation progressive de l'EC prévue\n- Passage en floraison dans ~1 semaine",
    "photoUrl": "https://example.com/photos/week3-growth-20250120.jpg"
}
```

### Récolte

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "🎉 Jour de récolte ! Cycle terminé après 14 semaines.\n\nRésumé du cycle :\n- Germination : 3 jours\n- Croissance végétative : 4 semaines\n- Floraison : 9 semaines\n- Rinçage final : 1 semaine\n\nRendement estimé : 450g (sec)\nQualité : excellente\n\nProchain cycle : démarrage dans 3 jours avec nouvelles graines.",
    "photoUrl": "https://example.com/photos/harvest-20250120.jpg"
}
```

## Exemples multilingues

### Anglais

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Daily check-up:\n- pH: 6.4 (stable)\n- EC: 1.7 mS/cm (good)\n- Water temp: 21°C (optimal)\n- Root health: excellent\n\nNo action needed today.",
    "photoUrl": null
}
```

### Espagnol

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Control diario:\n- pH: 6.5\n- EC: 1.8 mS/cm\n- Temperatura: 22°C\n- Estado: óptimo\n\nTodo funciona correctamente.",
    "photoUrl": null
}
```

## Exemples de contenu invalide (pour tests de validation)

### Content vide (❌ invalide)

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": ""
}
```

### Content null (❌ invalide)

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": null
}
```

### Reservoir manquant (❌ invalide)

```json
{
    "content": "Test sans réservoir"
}
```

### PhotoUrl trop long (❌ invalide)

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Test",
    "photoUrl": "https://example.com/photos/very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-very-long-url.jpg"
}
```

### Content trop long (❌ invalide - plus de 5000 caractères)

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": "Lorem ipsum dolor sit amet... (répéter jusqu'à dépasser 5000 caractères)"
}
```

## Scripts PowerShell pour créer des données de test

### Script 1 : Créer 5 entrées variées

```powershell
$API_URL = "http://localhost:8000"
$TOKEN = "votre_token"
$RESERVOIR_ID = "1"

$entries = @(
    @{
        content = "pH stable à 6.5"
        photoUrl = $null
    },
    @{
        content = "Ajout de nutriments : solution A (100ml) + solution B (50ml)"
        photoUrl = "https://example.com/photo1.jpg"
    },
    @{
        content = "Changement d'eau complet. Nouveaux paramètres : pH 6.4, EC 1.7 mS/cm"
        photoUrl = "https://example.com/photo2.jpg"
    },
    @{
        content = "Observation : croissance normale, racines saines"
        photoUrl = $null
    },
    @{
        content = "Maintenance hebdomadaire effectuée : nettoyage, vérification pompe, calibration pH-mètre"
        photoUrl = "https://example.com/photo3.jpg"
    }
)

foreach ($entry in $entries) {
    $body = @{
        reservoir = "/api/reservoirs/$RESERVOIR_ID"
        content = $entry.content
        photoUrl = $entry.photoUrl
    } | ConvertTo-Json

    try {
        $result = Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
            -Method Post `
            -Headers @{
                "Authorization" = "Bearer $TOKEN"
                "Content-Type" = "application/json"
            } `
            -Body $body

        Write-Host "✅ Entrée créée : ID $($result.id)" -ForegroundColor Green
        Start-Sleep -Seconds 1
    } catch {
        Write-Host "❌ Erreur : $($_.Exception.Message)" -ForegroundColor Red
    }
}
```

### Script 2 : Créer une entrée quotidienne automatique

```powershell
$API_URL = "http://localhost:8000"
$TOKEN = "votre_token"
$RESERVOIR_ID = "1"

# Générer un contenu avec la date du jour
$date = Get-Date -Format "dd/MM/yyyy"
$content = @"
Contrôle quotidien du $date :

Paramètres mesurés :
- pH : $(Get-Random -Minimum 60 -Maximum 70 | ForEach-Object { $_ / 10 })
- EC : $(Get-Random -Minimum 15 -Maximum 22 | ForEach-Object { $_ / 10 }) mS/cm
- Température : $(Get-Random -Minimum 20 -Maximum 24)°C
- Niveau d'eau : $(Get-Random -Minimum 60 -Maximum 95)%

État général : Normal
Prochaine vérification : demain
"@

$body = @{
    reservoir = "/api/reservoirs/$RESERVOIR_ID"
    content = $content
    photoUrl = $null
} | ConvertTo-Json

try {
    $result = Invoke-RestMethod -Uri "$API_URL/api/journal_entries" `
        -Method Post `
        -Headers @{
            "Authorization" = "Bearer $TOKEN"
            "Content-Type" = "application/json"
        } `
        -Body $body

    Write-Host "✅ Entrée quotidienne créée : ID $($result.id)" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur : $($_.Exception.Message)" -ForegroundColor Red
}
```

## Notes

-   Les URL de photos sont des exemples. Dans un environnement réel, utilisez des URLs valides ou implémentez un système d'upload.
-   Les emojis (🌱, ✓, 📊, etc.) sont supportés dans le contenu.
-   Le formatage (sauts de ligne \n) est conservé.
-   Adaptez les IDs de réservoirs selon votre base de données.
