# EPIC-2: JournalEntry Implementation

## 📋 Vue d'ensemble

Cette implémentation ajoute l'entité **JournalEntry** pour permettre aux utilisateurs de créer un journal de culture pour leurs réservoirs. Chaque entrée peut contenir du texte et optionnellement une photo.

## 🏗️ Architecture

### Entités créées/modifiées

#### 1. **JournalEntry** (nouvelle entité)

-   **Fichier**: `src/Entity/JournalEntry.php`
-   **Propriétés**:

    -   `id`: Identifiant unique
    -   `reservoir`: Relation ManyToOne vers Reservoir
    -   `content`: Texte de l'entrée (obligatoire, max 5000 caractères)
    -   `photoUrl`: URL/chemin de la photo (optionnel, max 500 caractères)
    -   `createdAt`: Date de création (automatique)
    -   `updatedAt`: Date de mise à jour (automatique)

-   **Validation**:

    -   `content`: NotBlank, Length(min: 1, max: 5000)
    -   `photoUrl`: Length(max: 500)
    -   `reservoir`: NotNull

-   **Opérations API Platform**:
    -   `GET /api/journal_entries`: Liste toutes les entrées de l'utilisateur
    -   `GET /api/journal_entries/{id}`: Récupère une entrée spécifique
    -   `POST /api/journal_entries`: Crée une nouvelle entrée
    -   `PUT /api/journal_entries/{id}`: Met à jour une entrée
    -   `DELETE /api/journal_entries/{id}`: Supprime une entrée

#### 2. **Reservoir** (modifié)

-   **Ajout**: Relation OneToMany vers JournalEntry
-   **Collection**: `journalEntries` exposée dans le groupe `reservoir:item`
-   **Méthodes ajoutées**:
    -   `getJournalEntries()`
    -   `addJournalEntry(JournalEntry $journalEntry)`
    -   `removeJournalEntry(JournalEntry $journalEntry)`

### Services créés

#### 1. **JournalEntryRepository**

-   **Fichier**: `src/Repository/JournalEntryRepository.php`
-   **Méthodes**:
    -   `findByUser(int $userId)`: Récupère toutes les entrées d'un utilisateur
    -   `findByReservoir(int $reservoirId)`: Récupère toutes les entrées d'un réservoir

#### 2. **JournalEntryQueryExtension**

-   **Fichier**: `src/Extension/JournalEntryQueryExtension.php`
-   **Rôle**: Filtre automatiquement les entrées de journal par propriétaire
-   **Logique de sécurité**:
    -   Joint: JournalEntry → Reservoir → Farm → User
    -   Filtre: `farm.owner = current_user`
    -   Bypass pour ROLE_ADMIN

## 🔒 Sécurité

### Niveau API Platform

Chaque opération est protégée par des règles de sécurité :

```php
// Lecture: l'utilisateur doit être propriétaire du réservoir
security: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user"

// Création: vérification post-dénormalisation
securityPostDenormalize: "is_granted('ROLE_USER') and object.getReservoir().getFarm().getOwner() == user"
```

### Niveau QueryExtension

Le `JournalEntryQueryExtension` filtre automatiquement tous les résultats :

-   S'applique aux collections (GET /api/journal_entries)
-   S'applique aux items (GET /api/journal_entries/{id})
-   Les admins (ROLE_ADMIN) peuvent voir toutes les entrées

## 📊 Modèle de données

```
User
  └─> Farm
       └─> Reservoir
            └─> JournalEntry
```

## 🚀 Installation

### 1. Générer la migration

```powershell
cd backend
php bin/console make:migration
```

### 2. Exécuter la migration

```powershell
php bin/console doctrine:migrations:migrate
```

### 3. Vérifier la structure de la table

```powershell
php bin/console doctrine:schema:validate
```

## 🧪 Tests manuels

### Prérequis

1. Avoir un utilisateur A avec token JWT
2. Avoir un utilisateur B avec token JWT
3. Avoir créé un réservoir pour l'utilisateur A

### Scénario de test

#### 1. Créer une entrée de journal (Utilisateur A)

**Requête**:

```http
POST /api/journal_entries
Authorization: Bearer <token_user_A>
Content-Type: application/json

{
  "reservoir": "/api/reservoirs/1",
  "content": "Ajout de nutriments aujourd'hui. pH ajusté à 6.5",
  "photoUrl": "https://example.com/photos/reservoir-20250120.jpg"
}
```

**Réponse attendue**: `201 Created`

```json
{
    "@context": "/api/contexts/JournalEntry",
    "@id": "/api/journal_entries/1",
    "@type": "JournalEntry",
    "id": 1,
    "reservoir": "/api/reservoirs/1",
    "content": "Ajout de nutriments aujourd'hui. pH ajusté à 6.5",
    "photoUrl": "https://example.com/photos/reservoir-20250120.jpg",
    "createdAt": "2025-11-20T10:30:00+00:00",
    "updatedAt": "2025-11-20T10:30:00+00:00"
}
```

#### 2. Lister les entrées (Utilisateur A)

**Requête**:

```http
GET /api/journal_entries
Authorization: Bearer <token_user_A>
```

**Réponse attendue**: `200 OK` avec la liste des entrées du user A uniquement

#### 3. Récupérer une entrée spécifique (Utilisateur A)

**Requête**:

```http
GET /api/journal_entries/1
Authorization: Bearer <token_user_A>
```

**Réponse attendue**: `200 OK` avec les détails de l'entrée

#### 4. Tentative d'accès par un autre utilisateur (Utilisateur B)

**Requête**:

```http
GET /api/journal_entries/1
Authorization: Bearer <token_user_B>
```

**Réponse attendue**: `403 Forbidden` ou `404 Not Found`

#### 5. Tentative de création pour un réservoir non possédé (Utilisateur B)

**Requête**:

```http
POST /api/journal_entries
Authorization: Bearer <token_user_B>
Content-Type: application/json

{
  "reservoir": "/api/reservoirs/1",
  "content": "Tentative d'écriture sur le réservoir de A"
}
```

**Réponse attendue**: `403 Forbidden`

#### 6. Mettre à jour une entrée (Utilisateur A)

**Requête**:

```http
PUT /api/journal_entries/1
Authorization: Bearer <token_user_A>
Content-Type: application/json

{
  "content": "Contenu modifié: pH stable à 6.5",
  "photoUrl": null
}
```

**Réponse attendue**: `200 OK` avec le contenu mis à jour

#### 7. Supprimer une entrée (Utilisateur A)

**Requête**:

```http
DELETE /api/journal_entries/1
Authorization: Bearer <token_user_A>
```

**Réponse attendue**: `204 No Content`

### Script de test avec curl

```powershell
# Variables
$TOKEN_A = "votre_token_user_a"
$TOKEN_B = "votre_token_user_b"
$API_URL = "http://localhost:8000"

# 1. Créer une entrée (User A)
curl -X POST "$API_URL/api/journal_entries" `
  -H "Authorization: Bearer $TOKEN_A" `
  -H "Content-Type: application/json" `
  -d '{
    "reservoir": "/api/reservoirs/1",
    "content": "Test journal entry",
    "photoUrl": "https://example.com/photo.jpg"
  }'

# 2. Lister les entrées (User A)
curl -X GET "$API_URL/api/journal_entries" `
  -H "Authorization: Bearer $TOKEN_A"

# 3. Tentative d'accès (User B) - devrait échouer
curl -X GET "$API_URL/api/journal_entries/1" `
  -H "Authorization: Bearer $TOKEN_B"

# 4. Tentative de création (User B) - devrait échouer
curl -X POST "$API_URL/api/journal_entries" `
  -H "Authorization: Bearer $TOKEN_B" `
  -H "Content-Type: application/json" `
  -d '{
    "reservoir": "/api/reservoirs/1",
    "content": "Unauthorized entry"
  }'
```

## 📝 Validation des données

### Règles de validation

| Champ       | Règles                     | Messages d'erreur                                                                                    |
| ----------- | -------------------------- | ---------------------------------------------------------------------------------------------------- |
| `reservoir` | NotNull                    | "L'entrée de journal doit être liée à un réservoir."                                                 |
| `content`   | NotBlank, Length(1-5000)   | "Le contenu de l'entrée ne peut pas être vide." / "Le contenu ne peut pas dépasser 5000 caractères." |
| `photoUrl`  | Length(max: 500), Optional | "L'URL de la photo ne peut pas dépasser 500 caractères."                                             |

### Exemple d'erreur de validation

**Requête** (content vide):

```json
{
    "reservoir": "/api/reservoirs/1",
    "content": ""
}
```

**Réponse**: `422 Unprocessable Entity`

```json
{
    "@context": "/api/contexts/ConstraintViolationList",
    "@type": "ConstraintViolationList",
    "hydra:title": "An error occurred",
    "violations": [
        {
            "propertyPath": "content",
            "message": "Le contenu de l'entrée ne peut pas être vide."
        }
    ]
}
```

## 🔄 Lifecycle Callbacks

### Automatismes

-   **`createdAt`**: Défini automatiquement dans le constructeur
-   **`updatedAt`**:
    -   Défini dans le constructeur
    -   Mis à jour automatiquement via `@ORM\PreUpdate` callback

```php
#[ORM\PreUpdate]
public function setUpdatedAtValue(): void
{
    $this->updatedAt = new \DateTimeImmutable();
}
```

## 📚 Groupes de sérialisation

| Groupe          | Utilisation         | Champs inclus                                          |
| --------------- | ------------------- | ------------------------------------------------------ |
| `journal:read`  | Lecture (GET)       | id, reservoir, content, photoUrl, createdAt, updatedAt |
| `journal:write` | Écriture (POST/PUT) | reservoir, content, photoUrl                           |
| `journal:item`  | Item détaillé       | Tous les champs de read                                |

## 🎯 Critères d'acceptation ✅

-   [x] `GET /api/journal_entries` retourne uniquement les entrées de l'utilisateur connecté
-   [x] `POST /api/journal_entries` permet de créer une nouvelle entrée avec reservoir précisé
-   [x] Un autre utilisateur ne peut pas accéder/créer/modifier les entrées d'un réservoir qu'il ne possède pas
-   [x] `createdAt` est automatiquement rempli à la création
-   [x] `updatedAt` est automatiquement mis à jour
-   [x] Validation du contenu (non vide)
-   [x] Relation inverse dans Reservoir (OneToMany journalEntries)

## 🐛 Troubleshooting

### Erreur: "Access Denied"

-   Vérifier que le token JWT est valide
-   Vérifier que l'utilisateur possède bien le réservoir
-   Vérifier les logs Symfony: `tail -f var/log/dev.log`

### Erreur: "Constraint Violation"

-   Vérifier que le reservoir existe et est accessible
-   Vérifier que le content n'est pas vide
-   Vérifier la longueur des champs

### QueryExtension ne filtre pas

-   Vérifier que l'autoconfiguration est activée dans `services.yaml`
-   Vérifier que la classe implémente bien les interfaces
-   Nettoyer le cache: `php bin/console cache:clear`

## 🚀 Évolutions futures

### Phase 2 (optionnel)

-   Upload de photos directement via l'API (multipart/form-data)
-   Miniatures automatiques pour les photos
-   Système de tags/catégories pour les entrées
-   Recherche full-text dans le contenu
-   Export PDF du journal de culture

### Phase 3 (optionnel)

-   Partage d'entrées entre utilisateurs
-   Commentaires sur les entrées
-   Notifications pour nouvelles entrées
-   Timeline visuelle du journal

## 📖 Références

-   [API Platform Documentation](https://api-platform.com/docs/)
-   [Symfony Security](https://symfony.com/doc/current/security.html)
-   [Doctrine Relations](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html)
