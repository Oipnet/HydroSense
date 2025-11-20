# HydroSense Backend API

Backend Symfony 7.3 avec API Platform et authentification JWT pour le projet HydroSense.

## 🚀 Prérequis

-   PHP 8.2 ou supérieur
-   Composer
-   PostgreSQL (ou SQLite pour le développement)
-   Symfony CLI (optionnel mais recommandé)

## 📦 Installation

### 1. Installation des dépendances

```bash
composer install
```

### 2. Configuration de l'environnement

Copiez le fichier `.env` et ajustez les variables d'environnement :

```bash
cp .env .env.local
```

Variables importantes à configurer dans `.env.local` :

-   `DATABASE_URL` : URL de connexion à votre base de données
-   `APP_SECRET` : Clé secrète de l'application (générez-en une unique)
-   `FRONTEND_URL` : URL de votre frontend (par défaut http://localhost:3000)

### 3. Génération des clés JWT

Les clés JWT sont déjà générées lors de l'installation. Si vous devez les régénérer :

```bash
php bin/console lexik:jwt:generate-keypair
```

### 4. Base de données

Créez la base de données et exécutez les migrations :

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Créer et exécuter les migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## 🔧 Développement

### Lancer le serveur de développement

#### Option 1 : Avec Symfony CLI (recommandé)

```bash
symfony serve
```

#### Option 2 : Avec PHP Built-in Server

```bash
php -S localhost:8000 -t public
```

L'API sera accessible à l'adresse : `http://localhost:8000`

### URLs importantes

-   **Documentation API** : `http://localhost:8000/api`
-   **Interface de test API** : `http://localhost:8000/api`
-   **Login JWT** : `POST http://localhost:8000/api/login_check`

## 🧪 Tests

### Tester que l'API répond

1. **Vérifier le status de l'API** :

    ```bash
    curl http://localhost:8000/api
    ```

2. **Tester une resource** (exemple avec l'entité Sensor) :
    ```bash
    curl http://localhost:8000/api/sensors
    ```

### Test d'authentification JWT

1. **Login** (une fois qu'un utilisateur est configuré) :

    ```bash
    curl -X POST http://localhost:8000/api/login_check \\
         -H "Content-Type: application/json" \\
         -d '{"username":"user@example.com","password":"password"}'
    ```

2. **Utiliser le token JWT** :
    ```bash
    curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \\
         http://localhost:8000/api/sensors
    ```

## 📚 Structure du projet

```
backend/
├── config/
│   ├── packages/
│   │   ├── api_platform.yaml      # Configuration API Platform
│   │   ├── doctrine.yaml          # Configuration base de données
│   │   ├── lexik_jwt_authentication.yaml  # Configuration JWT
│   │   ├── nelmio_cors.yaml       # Configuration CORS
│   │   └── security.yaml          # Configuration sécurité
│   ├── routes.yaml                # Routes de l'application
│   └── jwt/                       # Clés JWT (privée/publique)
├── docs/                          # 📖 Documentation technique
│   ├── README.md                  # Index de la documentation
│   ├── EPIC-2-CSV-IMPORT-IMPLEMENTATION.md
│   ├── TESTING-CSV-IMPORT.md
│   └── REFACTORING-STATE-PROCESSOR.md
├── examples/                      # 📁 Fichiers d'exemple
│   ├── measurements_sample.csv
│   └── measurements_with_errors.csv
├── src/
│   ├── Entity/                    # Entités Doctrine
│   │   ├── Reservoir.php         # Gestion des réservoirs
│   │   ├── Measurement.php       # Mesures (pH, EC, température)
│   │   ├── JournalEntry.php      # Journal de culture
│   │   ├── Alert.php             # Système d'alertes
│   │   └── Sensor.php            # Exemple d'entité API Resource
│   ├── Repository/               # Repositories Doctrine
│   ├── Extension/                # Query Extensions (sécurité automatique)
│   ├── State/                    # Providers & Processors API Platform
│   ├── Service/                  # Services métier
│   ├── Dto/                      # Data Transfer Objects
│   └── Controller/               # Contrôleurs personnalisés
├── public/
│   └── index.php                 # Point d'entrée
└── var/
    ├── cache/                    # Cache Symfony
    └── log/                      # Logs
```

## 📖 Documentation détaillée

Pour une documentation technique complète, consultez le dossier **[`docs/`](./docs/README.md)** qui contient :

-   **Journal de culture (JournalEntry)** : Système de notes et photos pour les réservoirs ([docs/README-JOURNAL-ENTRY.md](./docs/README-JOURNAL-ENTRY.md))
-   **Import CSV des mesures** : Implémentation complète et guide de test ([docs/EPIC-2-CSV-IMPORT-IMPLEMENTATION.md](./docs/EPIC-2-CSV-IMPORT-IMPLEMENTATION.md))
-   **Architecture State Processor** : Documentation du pattern Provider/Processor ([docs/REFACTORING-STATE-PROCESSOR.md](./docs/REFACTORING-STATE-PROCESSOR.md))
-   **Guides de test** : Exemples PowerShell pour tous les endpoints

## 🔒 Sécurité et JWT

### Configuration actuelle

-   **Firewall API** : `/api` protégé par JWT
-   **Route de login** : `/api/login_check` publique
-   **Documentation** : `/api/docs` publique
-   **CORS** : Configuré pour `localhost:3000` (frontend Nuxt)

### Prochaines étapes pour l'authentification

1. Créer une entité User :

    ```bash
    php bin/console make:user
    ```

2. Créer un contrôleur d'inscription :

    ```bash
    php bin/console make:controller RegistrationController
    ```

3. Configurer le provider d'utilisateurs dans `security.yaml`

## 🐳 Docker (optionnel)

Un fichier `compose.yaml` a été créé automatiquement. Pour utiliser Docker :

```bash
# Démarrer les services (base de données)
docker compose up -d

# Lancer l'application
symfony serve
```

## 🔧 Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Voir les routes disponibles
php bin/console debug:router

# Voir la configuration de sécurité
php bin/console debug:firewall

# Générer une nouvelle entité API Resource
php bin/console make:entity --api-resource

# Voir les logs en temps réel
tail -f var/log/dev.log
```

## 📝 Configuration CORS

Le CORS est configuré pour autoriser :

-   **Origin** : `localhost` et `127.0.0.1` sur tous les ports
-   **Méthodes** : GET, POST, PUT, PATCH, DELETE, OPTIONS
-   **Headers** : Content-Type, Authorization

Pour modifier la configuration CORS, éditez `config/packages/nelmio_cors.yaml`.

## 🚀 Prêt pour la production

Avant de déployer en production :

1. Configurez les variables d'environnement dans `.env.local`
2. Générez de nouvelles clés JWT sécurisées
3. Configurez une base de données de production
4. Activez HTTPS
5. Configurez le CORS pour votre domaine de production

## 📞 Support

Pour toute question concernant l'API HydroSense, consultez la documentation en ligne à `/api` ou contactez l'équipe de développement.
