# Commandes d'installation - Système d'Alertes

## Installation sur un environnement existant

Si vous installez le système d'alertes sur une installation HydroSense existante :

### 1. Mise à jour du code

```bash
# Pull les dernières modifications
git pull origin main

# Ou checkout la branche spécifique
git checkout 34-bug-la-doc-api-api-renvoie-500-call-to-a-member-function-getdescription-on-array
```

### 2. Installation des dépendances

```bash
cd backend
composer install
```

### 3. Migration de la base de données

```bash
# Vérifier les migrations en attente
php bin/console doctrine:migrations:status

# Exécuter la migration Alert
php bin/console doctrine:migrations:migrate --no-interaction

# Vérifier que tout est à jour
php bin/console doctrine:schema:validate
```

**Résultat attendu** :
```
✓ The mapping files are correct.
✓ The database schema is in sync with the mapping files.
```

### 4. Vérification de l'installation

```bash
# Vérifier que les routes Alert sont disponibles
php bin/console debug:router | grep alert

# Vérifier le service AnomalyDetector
php bin/console debug:container AnomalyDetector

# Vérifier l'extension de sécurité
php bin/console debug:container --tag=api_platform.doctrine.orm.query_extension.collection | grep Alert
```

### 5. Vider le cache (production)

```bash
php bin/console cache:clear --env=prod
```

---

## Installation depuis zéro (nouveau projet)

### 1. Cloner le repo

```bash
git clone https://github.com/Oipnet/HydroSense.git
cd HydroSense/backend
```

### 2. Configuration

```bash
# Copier le fichier d'environnement
cp .env .env.local

# Éditer .env.local avec vos paramètres DB
nano .env.local
```

### 3. Installation

```bash
# Installer les dépendances
composer install

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter toutes les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charger les fixtures (optionnel)
php bin/console doctrine:fixtures:load --no-interaction
```

### 4. Démarrage

```bash
# Démarrer le serveur
symfony server:start

# Ou avec PHP
php -S localhost:8000 -t public/
```

### 5. Test

```bash
# Vérifier l'API
curl http://localhost:8000/api

# Vérifier les alertes
curl http://localhost:8000/api/alerts
```

---

## Rollback (si nécessaire)

### Annuler la migration Alert

```bash
# Voir l'historique des migrations
php bin/console doctrine:migrations:list

# Annuler la dernière migration (Version20251120113530)
php bin/console doctrine:migrations:migrate prev --no-interaction

# Ou annuler vers une version spécifique
php bin/console doctrine:migrations:migrate DoctrineMigrations\\Version20251120105918 --no-interaction
```

### Supprimer les fichiers Alert (si rollback complet)

```bash
# Supprimer les entités/services
rm src/Entity/Alert.php
rm src/Repository/AlertRepository.php
rm src/Service/AnomalyDetector.php
rm src/Extension/AlertQueryExtension.php

# Restaurer les fichiers modifiés depuis Git
git checkout src/Entity/Farm.php
git checkout src/Entity/Reservoir.php
git checkout src/State/MeasurementPostProcessor.php

# Vider le cache
php bin/console cache:clear
```

---

## Migration de données existantes

### Générer des alertes pour les mesures historiques

Si vous avez déjà des mesures en base et souhaitez générer des alertes rétroactivement :

```php
// src/Command/GenerateHistoricalAlertsCommand.php
<?php

namespace App\Command;

use App\Entity\Measurement;
use App\Service\AnomalyDetector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-historical-alerts',
    description: 'Generate alerts for historical measurements'
)]
class GenerateHistoricalAlertsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AnomalyDetector $anomalyDetector
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $measurements = $this->entityManager->getRepository(Measurement::class)->findAll();
        $totalAlerts = 0;
        
        $io->progressStart(count($measurements));
        
        foreach ($measurements as $measurement) {
            $reservoir = $measurement->getReservoir();
            $cultureProfile = $reservoir->getFarm()->getCultureProfile();
            
            if ($cultureProfile) {
                $alerts = $this->anomalyDetector->detect($measurement, $cultureProfile);
                
                foreach ($alerts as $alert) {
                    $this->entityManager->persist($alert);
                    $totalAlerts++;
                }
            }
            
            $io->progressAdvance();
        }
        
        $this->entityManager->flush();
        $io->progressFinish();
        
        $io->success(sprintf('Generated %d alerts from %d measurements', $totalAlerts, count($measurements)));
        
        return Command::SUCCESS;
    }
}
```

Puis exécuter :

```bash
php bin/console app:generate-historical-alerts
```

---

## Docker (si utilisé)

### Mise à jour des containers

```bash
# Rebuilder les containers
docker-compose build

# Redémarrer
docker-compose down
docker-compose up -d

# Exécuter la migration dans le container
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### Accès au container

```bash
# Shell dans le container PHP
docker-compose exec php bash

# Puis exécuter les commandes normalement
php bin/console doctrine:migrations:status
```

---

## Environnement de production

### Checklist de déploiement

- [ ] Pull du code
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php bin/console doctrine:migrations:migrate --no-interaction`
- [ ] `php bin/console cache:clear --env=prod`
- [ ] `php bin/console cache:warmup --env=prod`
- [ ] Vérifier les logs : `tail -f var/log/prod.log`
- [ ] Tester l'API : `curl https://api.hydrosense.com/api/alerts`

### Monitoring post-déploiement

```bash
# Vérifier les erreurs
tail -f var/log/prod.log | grep ERROR

# Vérifier les détections d'anomalies
tail -f var/log/prod.log | grep "anomaly"

# Compter les alertes créées
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM alert"
```

---

## Tests post-installation

### Test rapide (automatisé)

```bash
# Créer ce script : test-alerts.sh

#!/bin/bash

API_URL="http://localhost:8000"
TOKEN="your_test_token"

echo "1. Testing GET /api/alerts..."
curl -s -X GET "$API_URL/api/alerts" -H "Authorization: Bearer $TOKEN" | jq

echo "2. Testing alert creation via measurement..."
curl -s -X POST "$API_URL/api/measurements" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 8.5,
    "ec": 1.5,
    "waterTemp": 20.0
  }' | jq

echo "3. Checking new alerts..."
curl -s -X GET "$API_URL/api/alerts?resolved=false" -H "Authorization: Bearer $TOKEN" | jq

echo "✓ Tests completed"
```

Exécuter :

```bash
chmod +x test-alerts.sh
./test-alerts.sh
```

---

## Troubleshooting

### Erreur : "Unknown named parameter $openapiContext"

**Solution** : Cette erreur a été corrigée. Si vous la rencontrez :

```bash
# Vérifier la version d'API Platform
composer show api-platform/core

# Mettre à jour si nécessaire
composer update api-platform/core
```

### Erreur : "Table alert does not exist"

**Solution** : La migration n'a pas été exécutée

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Erreur : "Call to undefined method Farm::getCultureProfile()"

**Solution** : Le cache n'a pas été vidé

```bash
php bin/console cache:clear
```

### Les alertes ne sont pas générées

**Vérifications** :

1. La ferme a-t-elle un CultureProfile ?
```bash
php bin/console doctrine:query:sql "SELECT id, name, culture_profile_id FROM farm"
```

2. Le service AnomalyDetector est-il appelé ?
```bash
tail -f var/log/dev.log | grep "anomaly"
```

3. Les valeurs sont-elles vraiment hors plage ?
```bash
php bin/console doctrine:query:sql "
  SELECT name, ph_min, ph_max, ec_min, ec_max 
  FROM culture_profile 
  WHERE id = 1
"
```

---

## Contact & Support

- **Documentation** : `backend/docs/EPIC-2-ALERT-IMPLEMENTATION.md`
- **Tests** : `backend/docs/TESTING-ALERT-API.md`
- **GitHub Issues** : https://github.com/Oipnet/HydroSense/issues
- **Email** : support@hydrosense.com

---

**Date de création** : 20 novembre 2025  
**Version** : 1.0.0  
**Auteur** : HydroSense Team
