# EPIC-2 : Entité CultureProfile - Documentation d'implémentation

## ✅ Implémentation complète

### 📋 Résumé

L'entité `CultureProfile` a été créée avec succès pour fournir un référentiel de profils de cultures hydroponiques avec leurs plages idéales (pH, EC, température).

---

## 🎯 Fichiers créés

### 1. Entité CultureProfile

**Fichier** : `src/Entity/CultureProfile.php`

**Champs** :

-   `id` (int, auto-increment)
-   `name` (string, unique, 100 caractères max)
-   `phMin` (float, 0-14)
-   `phMax` (float, 0-14)
-   `ecMin` (float, positif ou zéro, en mS/cm)
-   `ecMax` (float, positif, en mS/cm)
-   `waterTempMin` (float, 0-50°C)
-   `waterTempMax` (float, 0-50°C)

**Caractéristiques** :

-   ✅ API Platform configuré en **lecture seule** (GET, GET collection)
-   ✅ Validation complète avec contraintes Symfony
-   ✅ Documentation PHPDoc détaillée
-   ✅ Typage strict (PHP 8.2+)
-   ✅ Pagination activée (30 items par page)

### 2. Repository

**Fichier** : `src/Repository/CultureProfileRepository.php`

**Méthodes utiles** :

-   `findByName(string $name)` - Recherche par nom exact
-   `findAllOrderedByName()` - Liste triée alphabétiquement

### 3. Fixtures

**Fichier** : `src/DataFixtures/CultureProfileFixtures.php`

**14 profils de cultures inclus** :

1. Laitue
2. Basilic
3. Fraises
4. Tomates
5. Concombres
6. Poivrons
7. Épinards
8. Roquette
9. Menthe
10. Persil
11. Coriandre
12. Micro-pousses
13. Chou frisé (Kale)
14. Pak Choï

Les valeurs sont basées sur des recommandations professionnelles d'hydroponie.

### 4. Migration

**Fichier** : `migrations/Version20251120100452.php`

Crée la table `culture_profile` avec :

-   Tous les champs nécessaires
-   Index unique sur le nom
-   Support SQLite (actuel) et extensible aux autres SGBD

### 5. Configuration de sécurité

**Fichier** : `config/packages/security.yaml`

Ajout de la règle d'accès public :

```yaml
- { path: ^/api/culture_profiles, roles: PUBLIC_ACCESS, methods: [GET] }
```

---

## 🔧 Commandes exécutées

```bash
# 1. Installation du bundle de fixtures
composer require --dev doctrine/doctrine-fixtures-bundle

# 2. Génération de la migration
php bin/console make:migration

# 3. Exécution de la migration
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Chargement des fixtures
php bin/console doctrine:fixtures:load --no-interaction
```

---

## 🧪 Vérification et Tests

### Endpoints API disponibles

#### 1. Liste des profils (Collection)

```http
GET http://localhost:8000/api/culture_profiles
```

**Réponse** : HTTP 200 OK

```json
[
  {
    "id": 1,
    "name": "Laitue",
    "phMin": 5.5,
    "phMax": 6.5,
    "ecMin": 0.8,
    "ecMax": 1.2,
    "waterTempMin": 15.0,
    "waterTempMax": 20.0
  },
  ...
]
```

#### 2. Profil individuel

```http
GET http://localhost:8000/api/culture_profiles/{id}
```

**Exemple** : `GET http://localhost:8000/api/culture_profiles/1`

**Réponse** : HTTP 200 OK

```json
{
    "id": 1,
    "name": "Laitue",
    "phMin": 5.5,
    "phMax": 6.5,
    "ecMin": 0.8,
    "ecMax": 1.2,
    "waterTempMin": 15.0,
    "waterTempMax": 20.0
}
```

### Tests via PowerShell

```powershell
# Test collection
curl http://localhost:8000/api/culture_profiles

# Test item individuel
curl http://localhost:8000/api/culture_profiles/1

# Test avec Invoke-WebRequest (JSON formaté)
(Invoke-WebRequest -Uri "http://localhost:8000/api/culture_profiles").Content | ConvertFrom-Json | ConvertTo-Json
```

### Tests via navigateur

-   Collection : `http://localhost:8000/api/culture_profiles`
-   Item : `http://localhost:8000/api/culture_profiles/1`
-   Documentation OpenAPI : `http://localhost:8000/api/docs`

### Tests via Postman/Insomnia

1. Créer une requête GET
2. URL : `http://localhost:8000/api/culture_profiles`
3. Aucune authentification nécessaire
4. Headers automatiques

---

## ✅ Acceptance Criteria - Validation

| Critère                                        | Status | Détails                    |
| ---------------------------------------------- | ------ | -------------------------- |
| `GET /api/culture_profiles` retourne une liste | ✅     | 14 profils retournés       |
| Champs min/max correctement typés              | ✅     | Tous en float              |
| Champs exposés dans l'API                      | ✅     | Tous visibles dans JSON    |
| Lecture seule                                  | ✅     | Pas de POST/PUT/DELETE     |
| Validation des données                         | ✅     | Contraintes Assert actives |
| Documentation OpenAPI                          | ✅     | Généré automatiquement     |

---

## 📊 Structure de données - Exemple

### Laitue (profil complet)

```json
{
    "id": 1,
    "name": "Laitue",
    "phMin": 5.5,
    "phMax": 6.5,
    "ecMin": 0.8,
    "ecMax": 1.2,
    "waterTempMin": 15.0,
    "waterTempMax": 20.0
}
```

**Interprétation** :

-   pH optimal : 5.5 - 6.5
-   EC optimale : 0.8 - 1.2 mS/cm
-   Température eau optimale : 15°C - 20°C

---

## 🔄 Pour recharger les fixtures (développement)

```bash
# Supprime et recharge toutes les fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Ou avec confirmation
php bin/console doctrine:fixtures:load
```

---

## 🚀 Prochaines étapes possibles

1. **Frontend** : Intégrer l'affichage des profils dans Nuxt 3
2. **Filtres** : Ajouter des filtres par nom ou plages de valeurs
3. **Admin** : Créer une interface d'administration pour gérer les profils
4. **Relations** : Lier les profils aux capteurs ou aux analyses
5. **Images** : Ajouter des photos pour chaque culture
6. **Descriptions** : Ajouter des descriptions détaillées et conseils

---

## 📝 Notes techniques

-   **Base de données** : SQLite (dev) - facilement portable vers PostgreSQL/MySQL
-   **API Platform** : Version 4.2+
-   **Symfony** : Version 7.3
-   **PHP** : 8.2+
-   **Sérialisation** : JSON-LD par défaut (API Platform)
-   **CORS** : Configuré pour localhost
-   **Pagination** : 30 items par page (configurable dans l'entité)

---

## 🔒 Sécurité

-   ✅ Lecture publique autorisée (GET uniquement)
-   ✅ Écriture protégée par JWT (non exposée)
-   ✅ Validation stricte des données en entrée
-   ✅ Typage fort PHP 8.2+

---

**Date d'implémentation** : 20 novembre 2025  
**Branche** : `6-epic-2-user-authentification-jwt`  
**Issue** : #8 - [EPIC-2] Entité CultureProfile
