# EPIC-2 : Import CSV des Mesures - Documentation d'Implémentation

## 📋 Résumé

Cette implémentation permet d'importer des mesures depuis un fichier CSV vers un réservoir spécifique via l'API Platform.

**Issue GitHub :** #10 - [EPIC-2] Import CSV des mesures

## 🎯 Objectif

Permettre l'import facile d'un historique de mesures pour un réservoir via un fichier CSV.

## 🏗️ Architecture

### Entités créées

1. **`Reservoir`** (`src/Entity/Reservoir.php`)
   - Représente un réservoir d'eau
   - Exposé en ApiResource avec opérations CRUD
   - Contient une opération custom pour l'import CSV

2. **`Measurement`** (`src/Entity/Measurement.php`)
   - Représente une mesure (pH, EC, température)
   - Relation ManyToOne vers `Reservoir`
   - Champ `source` avec 3 valeurs possibles : `MANUAL`, `CSV_IMPORT`, `API_INTEGRATION`

### Services

**`CsvParserService`** (`src/Service/CsvParserService.php`)
- Parse le contenu CSV
- Valide le format et les données
- Crée les objets `Measurement`
- Gère les erreurs ligne par ligne

### States (API Platform)

**`CsvImportProvider`** (`src/State/CsvImportProvider.php`)
- Provider personnalisé pour extraire le fichier uploadé de la requête
- Crée un objet `CsvImportInput` avec le fichier

**`CsvImportProcessor`** (`src/State/CsvImportProcessor.php`)
- Processor qui gère la logique d'import
- Valide la présence et le type du fichier
- Orchestre le parsing et la persistance
- Retourne une réponse structurée

### DTO

**`CsvImportInput`** (`src/Dto/CsvImportInput.php`)
- DTO pour typer l'input de l'opération
- Contient la propriété `file` de type `UploadedFile`

## 🔗 Endpoint

```
POST /api/reservoirs/{id}/measurements/import
```

### Paramètres

- **Path parameter :** `id` (integer) - ID du réservoir
- **Form-data :** `file` (binary) - Fichier CSV

### Format CSV

```csv
measuredAt;ph;ec;waterTemp
2024-11-20T10:30:00;6.5;1.8;22.5
2024-11-20T14:00:00;6.8;1.9;23.0
```

**Spécifications :**
- Séparateur : `;` (point-virgule)
- En-tête obligatoire : `measuredAt;ph;ec;waterTemp`
- Format de date : ISO 8601 (`YYYY-MM-DDTHH:MM:SS`, `YYYY-MM-DD HH:MM:SS`, ou `YYYY-MM-DD`)
- Valeurs numériques : float ou integer (point ou virgule comme décimale)
- Au moins une valeur (ph, ec, waterTemp) doit être renseignée par ligne

### Réponses

#### ✅ Succès (200 OK)
```json
{
  "success": true,
  "imported": 5,
  "skipped": 0,
  "errors": []
}
```

#### ⚠️ Succès partiel (200 OK)
```json
{
  "success": true,
  "imported": 3,
  "skipped": 2,
  "errors": [
    "Line 3: Invalid date format for measuredAt: \"invalid-date\"",
    "Line 5: Invalid numeric value for ph: \"abc\""
  ]
}
```

#### ❌ Erreur de validation (400 Bad Request)
```json
{
  "success": false,
  "error": "No valid measurements found in CSV file",
  "errors": [
    "Invalid CSV header. Expected: measuredAt;ph;ec;waterTemp, Got: ..."
  ]
}
```

#### ❌ Réservoir introuvable (404 Not Found)
```json
{
  "error": "Reservoir not found"
}
```

## 🛠️ Stratégie de Gestion des Erreurs

L'implémentation adopte une **stratégie tolérante** :

1. Les lignes valides sont toujours importées
2. Les lignes invalides sont ignorées et reportées dans `errors`
3. Si aucune ligne n'est valide, retourne 400 Bad Request
4. Les erreurs sont détaillées avec le numéro de ligne et la raison

**Avantages :**
- Évite de bloquer un import complet à cause de quelques lignes défectueuses
- Fournit un feedback clair sur les problèmes
- Permet de corriger et réimporter uniquement les lignes en erreur

## 📁 Fichiers Créés/Modifiés

### Nouveaux fichiers

```
backend/
├── src/
│   ├── Entity/
│   │   ├── Reservoir.php              [NEW]
│   │   └── Measurement.php            [NEW]
│   ├── Repository/
│   │   ├── ReservoirRepository.php    [NEW]
│   │   └── MeasurementRepository.php  [NEW]
│   ├── Service/
│   │   └── CsvParserService.php       [NEW]
│   ├── State/
│   │   ├── CsvImportProvider.php      [NEW]
│   │   └── CsvImportProcessor.php     [NEW]
│   └── Dto/
│       └── CsvImportInput.php         [NEW]
├── migrations/
│   └── Version20251120102653.php  [NEW]
├── examples/
│   └── measurements_sample.csv    [NEW]
├── TESTING-CSV-IMPORT.md          [NEW]
└── EPIC-2-CSV-IMPORT-IMPLEMENTATION.md [NEW]
```

## 🚀 Installation et Configuration

### 1. Migrations de base de données

Les migrations ont déjà été générées et exécutées :

```bash
# Déjà fait :
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

Si vous devez recréer la base de données :

```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Aucune dépendance supplémentaire

Toutes les dépendances nécessaires sont déjà présentes dans le projet Symfony + API Platform.

### 3. Configuration JWT (si nécessaire)

Si vous souhaitez sécuriser l'endpoint, les opérations sont déjà configurées avec :
- `ROLE_USER` pour POST/PUT
- `ROLE_ADMIN` pour DELETE

## 🧪 Tests

Consultez le fichier **`TESTING-CSV-IMPORT.md`** pour des exemples détaillés de tests.

### Test rapide

1. Démarrer le serveur :
```bash
symfony server:start
```

2. Créer un réservoir :
```bash
curl -X POST http://localhost:8000/api/reservoirs \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Reservoir","capacity":1000}'
```

3. Importer le fichier CSV d'exemple :
```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@backend/examples/measurements_sample.csv"
```

4. Vérifier les mesures importées :
```bash
curl http://localhost:8000/api/measurements
```

## 📊 Schéma de Base de Données

### Table `reservoir`
| Colonne      | Type          | Nullable | Description                |
|--------------|---------------|----------|----------------------------|
| id           | INTEGER       | Non      | Clé primaire              |
| name         | VARCHAR(255)  | Non      | Nom du réservoir          |
| description  | TEXT          | Oui      | Description               |
| capacity     | FLOAT         | Oui      | Capacité en litres        |
| location     | VARCHAR(50)   | Oui      | Localisation              |

### Table `measurement`
| Colonne      | Type              | Nullable | Description                    |
|--------------|-------------------|----------|--------------------------------|
| id           | INTEGER           | Non      | Clé primaire                  |
| reservoir_id | INTEGER           | Non      | FK vers reservoir             |
| measured_at  | DATETIME          | Non      | Date/heure de la mesure       |
| ph           | FLOAT             | Oui      | pH (potentiel hydrogène)      |
| ec           | FLOAT             | Oui      | EC (conductivité électrique)  |
| water_temp   | FLOAT             | Oui      | Température de l'eau (°C)     |
| source       | VARCHAR(50)       | Non      | Source de la mesure           |
| created_at   | DATETIME          | Non      | Date de création              |

## 🔐 Sécurité

### Validation des fichiers

- ✅ Vérifie la présence du fichier
- ✅ Valide le type MIME (CSV, text/plain, etc.)
- ✅ Parse ligne par ligne (protection contre les gros fichiers)
- ✅ Valide chaque champ avant création d'entité

### Droits d'accès

Les opérations sont protégées selon les rôles :
- **GET** : Accès public (configurable)
- **POST/PUT** : `ROLE_USER`
- **DELETE** : `ROLE_ADMIN`
- **Import CSV** : Accès public (peut être sécurisé si nécessaire)

Pour sécuriser l'import, ajoutez dans `Reservoir.php` :
```php
new Post(
    // ...
    security: "is_granted('ROLE_USER')"
)
```

## 📝 Améliorations Futures Possibles

1. **Validation avancée**
   - Plages de valeurs acceptables (pH entre 0-14, etc.)
   - Détection de doublons (même reservoir + measuredAt)

2. **Performance**
   - Batch insert pour les gros fichiers
   - Import asynchrone avec Symfony Messenger

3. **Fonctionnalités**
   - Export CSV des mesures
   - Templates CSV téléchargeables
   - Prévisualisation avant import
   - Support de formats supplémentaires (Excel, JSON)

4. **Monitoring**
   - Logs d'import
   - Statistiques d'utilisation
   - Notification en cas d'erreurs récurrentes

## 🤝 Contribution

Ce code respecte les standards Symfony et API Platform. Pour toute modification :

1. Maintenir la cohérence avec l'architecture existante
2. Ajouter des tests unitaires/fonctionnels
3. Mettre à jour la documentation
4. Suivre les conventions PSR-12

## 📚 Ressources

- [API Platform - Custom Operations](https://api-platform.com/docs/core/operations/)
- [API Platform - State Providers & Processors](https://api-platform.com/docs/core/state-processors/)
- [Symfony - File Upload](https://symfony.com/doc/current/controller/upload_file.html)
- [Doctrine - Entity Relations](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/association-mapping.html)

---

**Auteur :** GitHub Copilot  
**Date :** 20 novembre 2024  
**Version Symfony :** 7.x  
**Version API Platform :** 3.x
