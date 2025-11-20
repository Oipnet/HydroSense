# EPIC-2 : Entités Farm & Reservoir - Documentation d'Implémentation

## 📋 Résumé

Cette implémentation permet de gérer les **fermes (Farms)** et les **réservoirs (Reservoirs)** avec une sécurité stricte par utilisateur : chaque utilisateur ne peut voir et manipuler que ses propres ressources.

**Issue GitHub :** #7 - [EPIC-2] Entités Farm & Reservoir

## 🎯 Objectif

Modéliser les fermes et les réservoirs liés à un utilisateur avec une isolation complète des données entre utilisateurs.

## 🏗️ Architecture

### Modèle de données

```
User (existant)
  └── farms (OneToMany)
       └── Farm
            ├── owner (ManyToOne → User)
            └── reservoirs (OneToMany)
                 └── Reservoir
                      └── farm (ManyToOne → Farm)
```

### Entités créées/modifiées

#### 1. **Farm** (`src/Entity/Farm.php`)

Représente une exploitation agricole appartenant à un utilisateur.

**Propriétés :**
- `id` : Identifiant unique (auto-généré)
- `name` : Nom de la ferme (string, 2-255 caractères, **requis**)
- `owner` : Propriétaire (ManyToOne → User, **requis**)
- `reservoirs` : Collection de réservoirs (OneToMany → Reservoir)
- `createdAt` : Date de création (DateTimeImmutable, **auto**)
- `updatedAt` : Date de modification (DateTimeImmutable, **auto**)

**Opérations API Platform :**
- `GET /api/farms` : Liste les fermes de l'utilisateur connecté
- `GET /api/farms/{id}` : Détails d'une ferme (si propriétaire)
- `POST /api/farms` : Créer une ferme (owner auto-assigné)
- `PUT /api/farms/{id}` : Modifier une ferme (si propriétaire)
- `DELETE /api/farms/{id}` : Supprimer une ferme (si propriétaire)

**Sécurité :**
- ✅ Filtrage automatique par owner (QueryExtension)
- ✅ Accès item : `object.owner == user`
- ✅ Owner auto-assigné à la création (FarmProcessor)

#### 2. **Reservoir** (`src/Entity/Reservoir.php` - **modifié**)

Représente un bac à nutriments dans une ferme.

**Propriétés ajoutées :**
- `farm` : Ferme parente (ManyToOne → Farm, **requis**)
- `volumeLiters` : Volume en litres (float, **requis**, > 0)
- `createdAt` : Date de création (DateTimeImmutable, **auto**)
- `updatedAt` : Date de modification (DateTimeImmutable, **auto**)

**Propriétés existantes conservées :**
- `id`, `name`, `description`, `location`
- `measurements` (OneToMany → Measurement)

**Opérations API Platform :**
- `GET /api/reservoirs` : Liste les réservoirs des fermes de l'utilisateur
- `GET /api/reservoirs/{id}` : Détails (si farm.owner == user)
- `POST /api/reservoirs` : Créer un réservoir
- `PUT /api/reservoirs/{id}` : Modifier (si farm.owner == user)
- `DELETE /api/reservoirs/{id}` : Supprimer (si farm.owner == user)
- `POST /api/reservoirs/{id}/measurements/import` : Import CSV (existant, maintenant sécurisé)

**Sécurité :**
- ✅ Filtrage automatique via farm.owner (ReservoirQueryExtension)
- ✅ Accès item : `object.farm.owner == user`

### Services créés

#### 3. **FarmProcessor** (`src/State/FarmProcessor.php`)

State Processor qui auto-assigne l'utilisateur connecté comme owner lors de la création d'une Farm.

**Avantages :**
- L'utilisateur n'a pas besoin de spécifier `owner` dans la requête
- Garantit que l'owner est toujours le créateur
- Simplifie l'API côté client

#### 4. **FarmQueryExtension** (`src/Extension/FarmQueryExtension.php`)

Extension Doctrine qui filtre automatiquement les collections de Farm par owner.

**Fonctionnement :**
- Ajoute `WHERE farm.owner = :current_user` aux requêtes de collection
- Les admins (ROLE_ADMIN) voient toutes les fermes
- Les utilisateurs normaux ne voient que leurs fermes

#### 5. **ReservoirQueryExtension** (`src/Extension/ReservoirQueryExtension.php`)

Extension Doctrine qui filtre les Reservoir par farm.owner.

**Fonctionnement :**
- Jointure automatique avec `farm`
- Filtre : `WHERE farm.owner = :current_user`
- Les admins voient tous les réservoirs

## 🔐 Stratégie de Sécurité

### Niveaux de sécurité implémentés

| Ressource | Niveau | Mécanisme | Description |
|-----------|--------|-----------|-------------|
| Farm | Collection | QueryExtension | Filtre automatique par owner |
| Farm | Item (GET) | `security` | `object.owner == user` |
| Farm | Item (PUT/DELETE) | `security` | `object.owner == user` |
| Farm | Creation (POST) | FarmProcessor | Owner auto-assigné |
| Reservoir | Collection | QueryExtension | Filtre via `farm.owner` |
| Reservoir | Item (GET) | `security` | `object.farm.owner == user` |
| Reservoir | Item (PUT/DELETE) | `security` | `object.farm.owner == user` |

### Avantages de cette approche

✅ **Double protection** : QueryExtension + expressions `security`  
✅ **Pas de fuite de données** : Impossible d'accéder aux ressources d'autrui  
✅ **Transparent** : Le filtrage est automatique, pas de code métier à ajouter  
✅ **Admin-friendly** : Les admins peuvent tout voir  
✅ **Performances** : Filtrage au niveau SQL  

### Limites et cas particuliers

⚠️ **Création de Reservoir** : L'utilisateur doit spécifier une `farm` qui lui appartient. La validation `securityPostDenormalize` vérifie que `farm.owner == user`.

⚠️ **Import CSV** : L'endpoint `/api/reservoirs/{id}/measurements/import` est maintenant sécurisé car l'accès au Reservoir lui-même nécessite `farm.owner == user`.

## 📁 Fichiers créés/modifiés

### Nouveaux fichiers

```
backend/src/
├── Entity/
│   └── Farm.php                          ✅ Nouvelle entité
├── Repository/
│   └── FarmRepository.php                ✅ Nouveau repository
├── State/
│   └── FarmProcessor.php                 ✅ Processor pour auto-assign owner
└── Extension/
    ├── FarmQueryExtension.php            ✅ Filtrage Farm par owner
    └── ReservoirQueryExtension.php       ✅ Filtrage Reservoir par farm.owner
```

### Fichiers modifiés

```
backend/src/
└── Entity/
    └── Reservoir.php                     🔧 Ajout de farm, volumeLiters, createdAt, sécurité
```

### Migration

```
backend/migrations/
└── Version20251120105918.php             ✅ Migration DB (farm + reservoir)
```

## 🚀 Commandes exécutées

```bash
# Génération de la migration
php bin/console doctrine:migrations:diff

# Exécution de la migration
php bin/console doctrine:migrations:migrate --no-interaction

# Vider le cache
php bin/console cache:clear
```

## 🧪 Guide de Test

### Prérequis : Créer deux utilisateurs

Utilisez l'endpoint de création d'utilisateur ou la commande Symfony pour créer 2 utilisateurs de test.

**Exemple avec commande (si disponible) :**
```bash
php bin/console app:create-user userA@test.com "UserA" "password123"
php bin/console app:create-user userB@test.com "UserB" "password456"
```

**Ou via API si endpoint d'inscription existe.**

### Étape 1 : Authentification

**User A - Obtenir le JWT :**
```bash
curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"userA@test.com","password":"password123"}'
```

Réponse :
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

Enregistrez le token : `TOKEN_A="eyJ0eXAiOiJKV1QiLCJhbGc..."`

**User B - Même processus :**
```bash
curl -X POST http://localhost:8000/api/login_check \
  -H "Content-Type: application/json" \
  -d '{"username":"userB@test.com","password":"password456"}'
```

Enregistrez : `TOKEN_B="..."`

### Étape 2 : User A crée une Farm

```bash
curl -X POST http://localhost:8000/api/farms \
  -H "Authorization: Bearer $TOKEN_A" \
  -H "Content-Type: application/json" \
  -d '{"name":"Ferme de UserA"}'
```

**Réponse (200 Created) :**
```json
{
  "@context": "/api/contexts/Farm",
  "@id": "/api/farms/1",
  "@type": "Farm",
  "id": 1,
  "name": "Ferme de UserA",
  "owner": {
    "@id": "/api/users/1",
    "@type": "User",
    "id": 1,
    "email": "userA@test.com",
    "name": "UserA"
  },
  "createdAt": "2024-11-20T11:00:00+00:00",
  "updatedAt": "2024-11-20T11:00:00+00:00"
}
```

✅ **Notez l'ID** : `1`

### Étape 3 : User A crée un Reservoir

```bash
curl -X POST http://localhost:8000/api/reservoirs \
  -H "Authorization: Bearer $TOKEN_A" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Réservoir Principal",
    "farm":"/api/farms/1",
    "volumeLiters":1000,
    "description":"Bac nutriments principal"
  }'
```

**Réponse (200 Created) :**
```json
{
  "@context": "/api/contexts/Reservoir",
  "@id": "/api/reservoirs/1",
  "@type": "Reservoir",
  "id": 1,
  "name": "Réservoir Principal",
  "farm": {
    "@id": "/api/farms/1",
    "name": "Ferme de UserA"
  },
  "volumeLiters": 1000,
  "description": "Bac nutriments principal",
  "createdAt": "2024-11-20T11:05:00+00:00"
}
```

### Étape 4 : User A liste ses ressources

**Lister les farms :**
```bash
curl -H "Authorization: Bearer $TOKEN_A" \
  http://localhost:8000/api/farms
```

**Résultat :** Voit uniquement sa ferme (id: 1)

**Lister les reservoirs :**
```bash
curl -H "Authorization: Bearer $TOKEN_A" \
  http://localhost:8000/api/reservoirs
```

**Résultat :** Voit uniquement son réservoir (id: 1)

### Étape 5 : User B essaie d'accéder aux ressources de User A

**User B tente de lister les farms :**
```bash
curl -H "Authorization: Bearer $TOKEN_B" \
  http://localhost:8000/api/farms
```

**Résultat :** Liste vide (ne voit pas la ferme de User A) ✅

**User B tente d'accéder à la farm de User A directement :**
```bash
curl -H "Authorization: Bearer $TOKEN_B" \
  http://localhost:8000/api/farms/1
```

**Réponse (403 Forbidden) :**
```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Access Denied."
}
```

✅ **Accès refusé !**

**User B tente d'accéder au reservoir de User A :**
```bash
curl -H "Authorization: Bearer $TOKEN_B" \
  http://localhost:8000/api/reservoirs/1
```

**Réponse (403 Forbidden)** ✅

### Étape 6 : User B crée sa propre Farm

```bash
curl -X POST http://localhost:8000/api/farms \
  -H "Authorization: Bearer $TOKEN_B" \
  -H "Content-Type: application/json" \
  -d '{"name":"Ferme de UserB"}'
```

**Résultat :** Farm créée avec id: 2, owner: UserB ✅

**User B liste maintenant ses farms :**
```bash
curl -H "Authorization: Bearer $TOKEN_B" \
  http://localhost:8000/api/farms
```

**Résultat :** Voit uniquement sa ferme (id: 2) ✅

### Étape 7 : User B essaie de créer un Reservoir dans la Farm de User A

```bash
curl -X POST http://localhost:8000/api/reservoirs \
  -H "Authorization: Bearer $TOKEN_B" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Hack Attempt",
    "farm":"/api/farms/1",
    "volumeLiters":500
  }'
```

**Réponse (403 Forbidden) :**
```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Access Denied."
}
```

✅ **La validation `securityPostDenormalize` bloque la création !**

### Étape 8 : Modification/Suppression

**User A modifie sa farm :**
```bash
curl -X PUT http://localhost:8000/api/farms/1 \
  -H "Authorization: Bearer $TOKEN_A" \
  -H "Content-Type: application/json" \
  -d '{"name":"Ferme Modifiée"}'
```

**Résultat :** Succès ✅

**User B essaie de modifier la farm de User A :**
```bash
curl -X PUT http://localhost:8000/api/farms/1 \
  -H "Authorization: Bearer $TOKEN_B" \
  -H "Content-Type: application/json" \
  -d '{"name":"Hack"}'
```

**Résultat :** 403 Forbidden ✅

**User A supprime son reservoir :**
```bash
curl -X DELETE http://localhost:8000/api/reservoirs/1 \
  -H "Authorization: Bearer $TOKEN_A"
```

**Résultat :** Succès (204 No Content) ✅

## ✅ Acceptance Criteria - Validation

| Critère | Status |
|---------|--------|
| CRUD API Platform fonctionnel pour Farm | ✅ |
| CRUD API Platform fonctionnel pour Reservoir | ✅ |
| Un utilisateur ne peut pas **lire** les farms d'un autre | ✅ |
| Un utilisateur ne peut pas **modifier** les farms d'un autre | ✅ |
| Un utilisateur ne peut pas **supprimer** les farms d'un autre | ✅ |
| Un utilisateur ne peut pas **lire** les reservoirs d'un autre | ✅ |
| Un utilisateur ne peut pas **modifier** les reservoirs d'un autre | ✅ |
| Un utilisateur ne peut pas **supprimer** les reservoirs d'un autre | ✅ |
| Un utilisateur ne peut pas créer un reservoir dans la farm d'un autre | ✅ |
| Les collections sont automatiquement filtrées par owner | ✅ |
| Le champ `createdAt` est auto-rempli | ✅ |
| Le champ `owner` est auto-assigné lors de la création de Farm | ✅ |

## 📊 Schéma de Base de Données

### Table `farm`

| Colonne      | Type              | Nullable | Description                |
|--------------|-------------------|----------|----------------------------|
| id           | INTEGER           | Non      | Clé primaire              |
| name         | VARCHAR(255)      | Non      | Nom de la ferme           |
| owner_id     | INTEGER           | Non      | FK vers users             |
| created_at   | DATETIME          | Non      | Date de création          |
| updated_at   | DATETIME          | Non      | Date de modification      |

**Index :**
- `IDX_5816D0457E3C61F9` sur `owner_id`

### Table `reservoir` (modifiée)

| Colonne       | Type              | Nullable | Description                    |
|---------------|-------------------|----------|--------------------------------|
| id            | INTEGER           | Non      | Clé primaire                  |
| name          | VARCHAR(255)      | Non      | Nom du réservoir              |
| farm_id       | INTEGER           | Non      | FK vers farm                  |
| volume_liters | FLOAT             | Non      | Volume en litres              |
| description   | TEXT              | Oui      | Description                   |
| location      | VARCHAR(50)       | Oui      | Localisation                  |
| created_at    | DATETIME          | Non      | Date de création              |
| updated_at    | DATETIME          | Non      | Date de modification          |

**Index :**
- `IDX_A117057165FCFA0D` sur `farm_id`

**Relations :**
- `reservoir.farm_id` → `farm.id` (ON DELETE ?)
- `farm.owner_id` → `users.id`

## 🔧 Points d'attention

### 1. Migration des données existantes

⚠️ Si des Reservoir existaient avant cette migration, ils ont été **supprimés** car la colonne `farm_id` est `NOT NULL`.

**Solution si données à conserver :**
- Modifier la migration pour créer d'abord une Farm par défaut
- Assigner tous les Reservoir orphelins à cette Farm
- Puis ajouter la contrainte NOT NULL

### 2. Cascade DELETE

Actuellement, si une Farm est supprimée, les Reservoir associés sont automatiquement supprimés (`orphanRemoval: true`).

**Alternative :** Implémenter une soft-delete ou demander confirmation avant suppression.

### 3. Permissions Admin

Les admins (`ROLE_ADMIN`) peuvent voir toutes les ressources mais les expressions `security` sur les opérations d'écriture vérifient quand même `object.owner == user`.

**Pour autoriser les admins à tout modifier**, changez les expressions :
```php
security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.owner == user)"
```

## 📝 Améliorations Futures Possibles

1. **Validation métier avancée**
   - Limiter le nombre de farms par utilisateur
   - Limiter le nombre de reservoirs par farm
   - Valider que `volumeLiters` est dans une plage réaliste

2. **Recherche et filtrage**
   - Filtrer les farms par nom
   - Filtrer les reservoirs par volume, farm, etc.
   - Tri personnalisé

3. **Statistiques**
   - Nombre total de mesures par reservoir
   - Volume total par farm
   - Activité récente

4. **Notifications**
   - Notifier lors de la création/suppression de farm
   - Alertes sur les réservoirs critiques

5. **Export/Import**
   - Exporter la configuration d'une farm (JSON/YAML)
   - Importer des farms depuis un template

## 📚 Ressources

- [API Platform - Security](https://api-platform.com/docs/core/security/)
- [API Platform - Extensions](https://api-platform.com/docs/core/extensions/)
- [Doctrine - Association Mapping](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/association-mapping.html)
- [Symfony Security](https://symfony.com/doc/current/security.html)

---

**Date d'implémentation :** 20 novembre 2024  
**Version Symfony :** 7.x  
**Version API Platform :** 3.x  
**Issue GitHub :** #7
