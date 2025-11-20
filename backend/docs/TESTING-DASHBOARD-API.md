# Guide de Test Rapide - Dashboard Endpoint

## 🚀 Test rapide avec curl

### Étape 1 : S'authentifier

```bash
# Obtenir un token JWT
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "votre-email@example.com",
    "password": "votre-mot-de-passe"
  }'
```

Récupérer le `token` de la réponse.

### Étape 2 : Appeler le dashboard

```bash
# Remplacer <TOKEN> par le token obtenu
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

## 📝 Réponse attendue

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

## 🔧 Commandes pour préparer les données de test

Si vous n'avez pas encore de données :

```bash
# 1. Créer une ferme
curl -X POST http://localhost:8000/api/farms \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ferme Test"
  }'

# 2. Créer un réservoir
curl -X POST http://localhost:8000/api/reservoirs \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Bac A",
    "farm": "/api/farms/1",
    "volumeLiters": 1000
  }'

# 3. Créer une mesure
curl -X POST http://localhost:8000/api/measurements \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "measuredAt": "2025-01-20T10:00:00+00:00",
    "ph": 6.2,
    "ec": 1.8,
    "waterTemp": 21.0
  }'

# 4. Tester le dashboard
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

## 🧪 Tests avec différents scénarios

### Scenario 1 : User sans réservoirs

**Attendu** :

```json
{
    "reservoirs": [],
    "alerts": {
        "total": 0,
        "critical": 0,
        "warn": 0
    }
}
```

### Scenario 2 : User avec réservoirs mais sans mesures

**Attendu** :

```json
{
    "reservoirs": [
        {
            "id": 1,
            "name": "Bac A",
            "farmName": "Ferme Test",
            "lastMeasurement": null,
            "status": "OK"
        }
    ],
    "alerts": {
        "total": 0,
        "critical": 0,
        "warn": 0
    }
}
```

### Scenario 3 : User avec alertes critiques

**Attendu** : Le statut du réservoir doit être `"CRITICAL"` et le compteur `"critical"` doit être > 0.

## ✅ Vérifications

-   [ ] Le endpoint `/api/dashboard` répond avec un code `200 OK`
-   [ ] La réponse contient les champs `reservoirs` et `alerts`
-   [ ] Seuls les réservoirs de l'utilisateur connecté apparaissent
-   [ ] La dernière mesure est correcte pour chaque réservoir
-   [ ] Le statut est calculé correctement (OK/WARN/CRITICAL)
-   [ ] Les compteurs d'alertes sont corrects
-   [ ] Un utilisateur non authentifié reçoit un `401 Unauthorized`

## 🐛 Dépannage

### Erreur 401 Unauthorized

-   Vérifier que le token JWT est valide
-   Vérifier que le token est envoyé dans le header `Authorization: Bearer <TOKEN>`

### Erreur 404 Not Found

-   Vérifier que la route `/api/dashboard` existe : `php bin/console debug:router | grep dashboard`
-   Vérifier que le serveur Symfony est démarré

### Erreur 500 Internal Server Error

-   Consulter les logs : `tail -f var/log/dev.log`
-   Vérifier que les repositories sont correctement injectés

### Pas de données retournées

-   Vérifier que l'utilisateur a des fermes et des réservoirs
-   Vérifier que les relations `farm.owner` sont correctes

## 📚 Ressources

-   Documentation OpenAPI : http://localhost:8000/api/docs
-   Logs Symfony : `backend/var/log/dev.log`
-   Documentation complète : `backend/docs/EPIC-2-DASHBOARD-IMPLEMENTATION.md`
