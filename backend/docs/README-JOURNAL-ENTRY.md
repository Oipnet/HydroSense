# 📔 JournalEntry - Guide rapide

## ✅ Implémentation terminée

L'entité **JournalEntry** a été implémentée avec succès pour permettre aux utilisateurs de tenir un journal de culture pour leurs réservoirs.

## 📦 Fichiers créés/modifiés

### Nouveaux fichiers

-   ✅ `src/Entity/JournalEntry.php` - Entité principale
-   ✅ `src/Repository/JournalEntryRepository.php` - Repository avec méthodes de recherche
-   ✅ `src/Extension/JournalEntryQueryExtension.php` - Filtrage automatique par propriétaire
-   ✅ `migrations/Version20251120115107.php` - Migration de la base de données
-   ✅ `docs/EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md` - Documentation détaillée
-   ✅ `docs/TESTING-JOURNAL-ENTRY-API.md` - Guide de test avec exemples PowerShell

### Fichiers modifiés

-   ✅ `src/Entity/Reservoir.php` - Ajout relation OneToMany vers JournalEntry

## 🗄️ Structure de la table

```sql
CREATE TABLE journal_entry (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reservoir_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    photo_url VARCHAR(500),
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (reservoir_id) REFERENCES reservoir (id)
);
```

## 🚀 Commandes exécutées

```powershell
# Migration générée et appliquée
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Schéma validé
php bin/console doctrine:schema:validate
```

**Résultat** : ✅ Le schéma est synchronisé avec les entités

## 🔌 Endpoints disponibles

| Méthode  | Endpoint                    | Description                               |
| -------- | --------------------------- | ----------------------------------------- |
| `GET`    | `/api/journal_entries`      | Liste toutes les entrées de l'utilisateur |
| `GET`    | `/api/journal_entries/{id}` | Récupère une entrée spécifique            |
| `POST`   | `/api/journal_entries`      | Crée une nouvelle entrée                  |
| `PUT`    | `/api/journal_entries/{id}` | Met à jour une entrée                     |
| `DELETE` | `/api/journal_entries/{id}` | Supprime une entrée                       |

## 📝 Exemple d'utilisation

### Créer une entrée

```powershell
$body = @{
    reservoir = "/api/reservoirs/1"
    content = "pH ajusté à 6.5, ajout de nutriments"
    photoUrl = "https://example.com/photo.jpg"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/journal_entries" `
    -Method Post `
    -Headers @{
        "Authorization" = "Bearer votre_token"
        "Content-Type" = "application/json"
    } `
    -Body $body
```

### Lister les entrées

```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/journal_entries" `
    -Method Get `
    -Headers @{ "Authorization" = "Bearer votre_token" }
```

## 🔒 Sécurité implémentée

### Niveau API Platform

-   ✅ Toutes les opérations nécessitent `ROLE_USER`
-   ✅ Vérification que l'utilisateur possède le réservoir via `farm.owner == user`
-   ✅ `securityPostDenormalize` pour POST (vérification après création)

### Niveau QueryExtension

-   ✅ Filtrage automatique des résultats par propriétaire
-   ✅ Joints automatiques : JournalEntry → Reservoir → Farm → User
-   ✅ Bypass pour `ROLE_ADMIN`

### Tests de sécurité

-   ✅ User A peut créer/lire/modifier/supprimer ses entrées
-   ✅ User B ne peut pas accéder aux entrées de User A
-   ✅ User B ne peut pas créer d'entrées pour les réservoirs de User A

## ✅ Validation des données

| Champ       | Règles                         |
| ----------- | ------------------------------ |
| `reservoir` | Obligatoire (NotNull)          |
| `content`   | Obligatoire, 1-5000 caractères |
| `photoUrl`  | Optionnel, max 500 caractères  |
| `createdAt` | Auto-rempli à la création      |
| `updatedAt` | Auto-rempli et mis à jour      |

## 📚 Documentation

Pour plus de détails, consultez :

-   **Documentation complète** : `docs/EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md`
-   **Guide de test** : `docs/TESTING-JOURNAL-ENTRY-API.md`

## 🎯 Critères d'acceptation

| Critère                                                                   | Statut |
| ------------------------------------------------------------------------- | ------ |
| GET /api/journal_entries retourne uniquement les entrées de l'utilisateur | ✅     |
| POST /api/journal_entries permet de créer une entrée                      | ✅     |
| Un autre utilisateur ne peut pas accéder/créer/modifier les entrées       | ✅     |
| createdAt automatiquement rempli                                          | ✅     |
| Validation du contenu                                                     | ✅     |
| Relation inverse dans Reservoir                                           | ✅     |

## 🧪 Comment tester

### 1. Prérequis

-   Serveur Symfony lancé : `symfony server:start`
-   2 utilisateurs créés avec tokens JWT
-   Au moins 1 réservoir par utilisateur

### 2. Tests rapides

Exécutez les commandes PowerShell dans `docs/TESTING-JOURNAL-ENTRY-API.md`

### 3. Vérification manuelle

1. Créez une entrée via POST
2. Listez les entrées via GET
3. Testez avec un autre utilisateur (doit échouer)

## 🐛 Troubleshooting

**Problème** : Erreur 403 lors de la création  
**Solution** : Vérifiez que le réservoir appartient bien à l'utilisateur connecté

**Problème** : QueryExtension ne filtre pas  
**Solution** : Nettoyez le cache : `php bin/console cache:clear`

**Problème** : Erreur de validation  
**Solution** : Vérifiez que le content n'est pas vide et fait moins de 5000 caractères

## 🚀 Prochaines étapes (optionnel)

-   [ ] Upload de photos via multipart/form-data
-   [ ] Filtres de recherche (date, mots-clés)
-   [ ] Tri des entrées
-   [ ] Export PDF du journal
-   [ ] Système de tags

## 📞 Support

Pour toute question sur l'implémentation, consultez :

-   Les docblocks dans `src/Entity/JournalEntry.php`
-   La documentation complète dans `docs/`
-   Les exemples dans `docs/TESTING-JOURNAL-ENTRY-API.md`

---

**Implémenté le** : 20 novembre 2025  
**Issue GitHub** : #12 - [EPIC-2] Entité JournalEntry (journal de culture)  
**Status** : ✅ Prêt pour production
