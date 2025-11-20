# EPIC-2 - Dashboard Endpoint Implementation

## 📋 Vue d'ensemble

L'endpoint `/api/dashboard` fournit une **vue synthèse** de l'état de la ferme pour l'utilisateur connecté. Il agrège les données des réservoirs, mesures et alertes pour afficher un tableau de bord complet.

## 🎯 Issue

**Issue #13** - [EPIC-2] Endpoint Dashboard (vue synthèse backend)

## 📁 Structure des fichiers

```
backend/src/
├── ApiResource/
│   └── Dashboard.php                    # Ressource API Platform
├── Dto/
│   └── Dashboard/
│       ├── DashboardResponse.php        # DTO principal de réponse
│       ├── ReservoirSummary.php         # Résumé d'un réservoir
│       ├── LastMeasurementView.php      # Vue de la dernière mesure
│       └── AlertsSummary.php            # Résumé des alertes
└── State/
    └── DashboardProvider.php            # Provider qui fournit les données
```

## 🔌 Endpoint

### GET /api/dashboard

**Sécurité** : `ROLE_USER` requis (utilisateur authentifié)

**Description** : Retourne une vue synthétique des fermes, réservoirs, mesures et alertes de l'utilisateur connecté.

**Réponse (200 OK)** :

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
        },
        {
            "id": 2,
            "name": "Bac tomate B",
            "farmName": "Ferme Nord",
            "lastMeasurement": {
                "measuredAt": "2025-01-10T09:15:00+00:00",
                "ph": 7.2,
                "ec": 2.8,
                "waterTemp": 22.5
            },
            "status": "CRITICAL"
        }
    ],
    "alerts": {
        "total": 3,
        "critical": 1,
        "warn": 2
    }
}
```

## 🎯 Logique métier

### Calcul du statut d'un réservoir

Le statut de chaque réservoir est calculé en fonction des alertes **non résolues** :

| Condition                                                  | Statut     |
| ---------------------------------------------------------- | ---------- |
| Au moins une alerte **CRITICAL** non résolue               | `CRITICAL` |
| Au moins une alerte **WARN** non résolue (pas de CRITICAL) | `WARN`     |
| Aucune alerte ou seulement des alertes **INFO**            | `OK`       |

### Agrégation des alertes

Le compteur d'alertes inclut :

-   **total** : Nombre total d'alertes non résolues
-   **critical** : Nombre d'alertes CRITICAL non résolues
-   **warn** : Nombre d'alertes WARN non résolues

### Dernière mesure

Pour chaque réservoir, on récupère la mesure la plus récente (triée par `measuredAt DESC`).

## 🔐 Sécurité

-   ✅ L'endpoint est protégé par `is_granted('ROLE_USER')`
-   ✅ Les données sont automatiquement filtrées : seuls les réservoirs appartenant aux fermes de l'utilisateur sont retournés
-   ✅ Pas de risque de fuite de données entre utilisateurs
-   ✅ Aucun paramètre d'ID dans l'URL : tout est basé sur le user authentifié

## 🧪 Tests manuels

### Prérequis

1. Backend Symfony démarré
2. Base de données avec des données de test
3. Token JWT valide pour l'authentification

### Scénario de test 1 : User avec des réservoirs

```bash
# 1. Créer un utilisateur (si nécessaire)
POST /api/users
{
  "email": "user.test@example.com",
  "name": "User Test",
  "password": "password123"
}

# 2. S'authentifier
POST /api/login
{
  "email": "user.test@example.com",
  "password": "password123"
}
# => Récupérer le token JWT

# 3. Créer une ferme
POST /api/farms
Authorization: Bearer <TOKEN>
{
  "name": "Ferme Test"
}

# 4. Créer des réservoirs
POST /api/reservoirs
Authorization: Bearer <TOKEN>
{
  "name": "Bac A",
  "farm": "/api/farms/1",
  "volumeLiters": 1000
}

POST /api/reservoirs
Authorization: Bearer <TOKEN>
{
  "name": "Bac B",
  "farm": "/api/farms/1",
  "volumeLiters": 1500
}

# 5. Ajouter des mesures
POST /api/measurements
Authorization: Bearer <TOKEN>
{
  "reservoir": "/api/reservoirs/1",
  "measuredAt": "2025-01-15T10:00:00+00:00",
  "ph": 6.2,
  "ec": 1.8,
  "waterTemp": 21.0
}

POST /api/measurements
Authorization: Bearer <TOKEN>
{
  "reservoir": "/api/reservoirs/2",
  "measuredAt": "2025-01-15T11:00:00+00:00",
  "ph": 7.5,
  "ec": 2.9,
  "waterTemp": 23.0
}

# 6. Appeler le dashboard
GET /api/dashboard
Authorization: Bearer <TOKEN>
```

**Résultat attendu** :

-   Les 2 réservoirs apparaissent
-   Chaque réservoir a sa dernière mesure
-   Le statut reflète les alertes (si présentes)
-   Les compteurs d'alertes sont corrects

### Scénario de test 2 : Isolation des users

```bash
# 1. Créer un second utilisateur
POST /api/users
{
  "email": "user.b@example.com",
  "name": "User B",
  "password": "password456"
}

# 2. S'authentifier en tant que User B
POST /api/login
{
  "email": "user.b@example.com",
  "password": "password456"
}
# => Récupérer le token JWT de B

# 3. Créer une ferme pour User B
POST /api/farms
Authorization: Bearer <TOKEN_B>
{
  "name": "Ferme User B"
}

# 4. Appeler le dashboard avec le token de User B
GET /api/dashboard
Authorization: Bearer <TOKEN_B>
```

**Résultat attendu** :

-   User B ne voit QUE ses propres réservoirs
-   Les réservoirs de User A n'apparaissent PAS

### Scénario de test 3 : Vérifier les statuts

```bash
# Supposons que Reservoir ID=2 a des alertes critiques

# 1. Créer une alerte critique manuellement (ou via mesure hors range)
# Si vous avez un CultureProfile, créez une mesure qui déclenche une alerte

# 2. Appeler le dashboard
GET /api/dashboard
Authorization: Bearer <TOKEN>
```

**Résultat attendu** :

-   Le réservoir avec alerte CRITICAL affiche `"status": "CRITICAL"`
-   Les compteurs d'alertes reflètent : `"critical": 1`

### Scénario de test 4 : Sans données

```bash
# Avec un user qui n'a pas encore de ferme/réservoir

GET /api/dashboard
Authorization: Bearer <TOKEN_NEW_USER>
```

**Résultat attendu** :

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

## 📊 Cas d'usage typiques

### 1. Page d'accueil du dashboard frontend

```javascript
// Nuxt 3 - Composable
export const useDashboard = () => {
    const { $api } = useNuxtApp();

    const fetchDashboard = async () => {
        const response = await $api.get("/dashboard");
        return response.data;
    };

    return { fetchDashboard };
};

// Page dashboard
const dashboard = await useDashboard().fetchDashboard();
// Afficher dashboard.reservoirs dans une grille
// Afficher dashboard.alerts dans un widget d'alertes
```

### 2. Surveillance en temps réel

-   Rafraîchir le dashboard toutes les 30 secondes
-   Afficher une notification si `alerts.critical > 0`
-   Mettre en surbrillance les réservoirs en statut `CRITICAL`

### 3. Vue mobile

-   Liste scrollable des réservoirs
-   Badges colorés selon le statut (vert=OK, orange=WARN, rouge=CRITICAL)
-   Accès rapide aux alertes depuis le widget

## 🔍 Debugging

### Logs à surveiller

```bash
# Dans le terminal Symfony
tail -f var/log/dev.log | grep Dashboard
```

### Commandes utiles

```bash
# Vérifier que la route est bien enregistrée
php bin/console debug:router | grep dashboard

# Résultat attendu:
# dashboard    GET    ANY    ANY    /api/dashboard

# Vérifier les services
php bin/console debug:container DashboardProvider
```

### Erreurs courantes

| Erreur                                         | Cause                            | Solution                    |
| ---------------------------------------------- | -------------------------------- | --------------------------- |
| `401 Unauthorized`                             | Pas de token JWT ou token expiré | Se ré-authentifier          |
| `RuntimeException: User must be authenticated` | Security ne retourne pas d'user  | Vérifier la config JWT      |
| Pas de données                                 | Aucun réservoir pour l'user      | Créer des fermes/réservoirs |
| `500 Internal Server Error`                    | Erreur SQL ou logique métier     | Vérifier les logs Symfony   |

## 📚 Documentation API

La documentation OpenAPI complète est disponible à :

```
GET /api/docs
```

Chercher l'endpoint `/api/dashboard` pour voir :

-   Le schéma de réponse détaillé
-   Les exemples de réponse
-   Les codes d'erreur possibles

## ✅ Checklist d'implémentation

-   [x] Création des DTOs (`DashboardResponse`, `ReservoirSummary`, `LastMeasurementView`, `AlertsSummary`)
-   [x] Création du Provider `DashboardProvider`
-   [x] Création de la ressource API Platform `Dashboard`
-   [x] Configuration de la sécurité (`ROLE_USER`)
-   [x] Documentation OpenAPI intégrée
-   [x] Calcul du statut basé sur les alertes
-   [x] Agrégation des compteurs d'alertes
-   [x] Récupération de la dernière mesure par réservoir
-   [x] Filtrage automatique par utilisateur

## 🚀 Prochaines étapes

1. **Tests fonctionnels** : Créer des tests automatisés avec PHPUnit
2. **Optimisation** : Ajouter un cache Redis pour le dashboard (TTL 30s)
3. **Pagination** : Si un user a >100 réservoirs, ajouter la pagination
4. **Filtres** : Permettre de filtrer par ferme, statut, etc.
5. **Websockets** : Push updates en temps réel quand une nouvelle alerte est créée

## 📝 Notes techniques

-   **Performance** : Le Provider fait 1 requête pour les réservoirs + 1 requête par réservoir pour la dernière mesure. Pour optimiser, on pourrait faire une seule requête avec un `LEFT JOIN` et `GROUP BY`.
-   **Cache** : Pas de cache pour l'instant, mais recommandé en production.
-   **Serialization** : Utilise les groupes `dashboard:read` pour contrôler la sortie JSON.
-   **API Platform** : Utilise un Provider custom plutôt qu'une extension Doctrine car c'est une agrégation cross-entity.

## 📞 Support

Pour toute question ou problème :

1. Vérifier les logs Symfony : `var/log/dev.log`
2. Vérifier la documentation OpenAPI : `/api/docs`
3. Consulter les issues GitHub du projet

---

**Auteur** : GitHub Copilot  
**Date** : 20 novembre 2025  
**Version** : 1.0  
**Issue** : #13 - [EPIC-2] Endpoint Dashboard
