# EPIC-2 : Système d'Alertes Automatiques

## Vue d'ensemble

Le système d'alertes automatiques détecte les anomalies dans les mesures hydroponiques en temps réel et génère des alertes lorsque les valeurs pH, EC (électroconductivité) ou température de l'eau sortent des plages recommandées définies dans le profil de culture.

## Architecture

### Composants créés

1. **Entity/Alert.php** - Entité représentant une alerte
2. **Repository/AlertRepository.php** - Repository pour les requêtes personnalisées
3. **Service/AnomalyDetector.php** - Service de détection d'anomalies
4. **Extension/AlertQueryExtension.php** - Filtrage de sécurité automatique
5. **State/MeasurementPostProcessor.php** - Modifié pour intégrer la détection

### Schéma de données

```
Farm
 ├─ cultureProfile (ManyToOne → CultureProfile) [NOUVEAU]
 └─ reservoirs (OneToMany)
      ├─ measurements (OneToMany)
      └─ alerts (OneToMany) [NOUVEAU]

Alert
 ├─ id
 ├─ reservoir (ManyToOne → Reservoir)
 ├─ measurement (ManyToOne → Measurement)
 ├─ type (PH_OUT_OF_RANGE | EC_OUT_OF_RANGE | TEMP_OUT_OF_RANGE)
 ├─ severity (INFO | WARN | CRITICAL)
 ├─ message (texte descriptif)
 ├─ measuredValue (valeur mesurée)
 ├─ expectedMin (valeur minimale attendue)
 ├─ expectedMax (valeur maximale attendue)
 ├─ createdAt (date de création)
 └─ resolvedAt (date de résolution, nullable)
```

## Workflow automatique

### 1. Création d'une mesure

```
POST /api/measurements
{
  "reservoir": "/api/reservoirs/1",
  "ph": 7.5,
  "ec": 2.1,
  "waterTemp": 22.5,
  "measuredAt": "2025-11-20T12:00:00Z"
}
```

### 2. Traitement par MeasurementPostProcessor

1. **Validation** - Vérification des droits d'accès
2. **Persistance** - Sauvegarde de la mesure en base
3. **Détection d'anomalies** - Appel automatique à `AnomalyDetector`
4. **Création d'alertes** - Génération et persistance des alertes détectées

### 3. Détection par AnomalyDetector

```php
// Récupération du CultureProfile via Farm
$cultureProfile = $reservoir->getFarm()->getCultureProfile();

// Vérification pH
if ($ph < $cultureProfile->getPhMin() || $ph > $cultureProfile->getPhMax()) {
    // Création d'une alerte PH_OUT_OF_RANGE
}

// Vérification EC
if ($ec < $cultureProfile->getEcMin() || $ec > $cultureProfile->getEcMax()) {
    // Création d'une alerte EC_OUT_OF_RANGE
}

// Vérification température
if ($temp < $cultureProfile->getWaterTempMin() || $temp > $cultureProfile->getWaterTempMax()) {
    // Création d'une alerte TEMP_OUT_OF_RANGE
}
```

### 4. Calcul de la sévérité

La sévérité est calculée en fonction du pourcentage de déviation par rapport à la plage acceptable :

- **INFO** : Déviation < 10% (légère anomalie)
- **WARN** : Déviation 10-25% (anomalie modérée)
- **CRITICAL** : Déviation > 25% (anomalie sévère)

**Formule** :
```
deviationPercent = (deviation / rangeWidth) * 100
où deviation = distance en dehors de la plage [min, max]
```

## Endpoints API

### GET /api/alerts

Récupère toutes les alertes de l'utilisateur connecté.

**Filtres disponibles** :
- `?type=PH_OUT_OF_RANGE` - Filtrer par type d'alerte
- `?severity=CRITICAL` - Filtrer par sévérité
- `?resolved=false` - Filtrer les alertes non résolues
- `?reservoir=/api/reservoirs/1` - Filtrer par réservoir
- `?createdAt[after]=2025-11-20` - Filtrer par date de création
- `?order[createdAt]=desc` - Trier par date (desc par défaut)

**Exemple de réponse** :
```json
{
  "hydra:member": [
    {
      "@id": "/api/alerts/1",
      "@type": "Alert",
      "id": 1,
      "reservoir": "/api/reservoirs/1",
      "measurement": "/api/measurements/42",
      "type": "PH_OUT_OF_RANGE",
      "severity": "WARN",
      "message": "pH level 7.50 is outside the recommended range [5.50 - 6.50] for Laitue",
      "measuredValue": 7.5,
      "expectedMin": 5.5,
      "expectedMax": 6.5,
      "createdAt": "2025-11-20T12:00:00+00:00",
      "resolvedAt": null,
      "resolved": false
    }
  ],
  "hydra:totalItems": 1
}
```

### GET /api/alerts/{id}

Récupère une alerte spécifique.

**Réponse** : Même structure avec détails complets de la mesure associée.

### PATCH /api/alerts/{id}

Marque une alerte comme résolue.

**Corps de la requête** :
```json
{
  "resolvedAt": "2025-11-20T14:30:00Z"
}
```

**Réponse** : Alerte mise à jour avec `resolved: true`

## Sécurité

### Règles d'accès

1. **Collection** : `is_granted('ROLE_USER')`
2. **Item** : `object.reservoir.farm.owner == user`
3. **Modification** : Propriétaire uniquement

### Filtrage automatique (AlertQueryExtension)

Toutes les requêtes Alert sont automatiquement filtrées pour ne retourner que les alertes liées aux réservoirs appartenant à l'utilisateur connecté.

**Chaîne de filtrage** :
```
Alert → Reservoir → Farm → Owner == current_user
```

## Configuration d'une ferme

Pour activer la détection d'anomalies, une ferme doit avoir un profil de culture configuré :

```http
PATCH /api/farms/{id}
{
  "cultureProfile": "/api/culture_profiles/1"
}
```

**Sans CultureProfile** :
- Aucune alerte n'est générée
- Un log INFO est enregistré

## Tests

### Scénario 1 : Mesure normale (aucune alerte)

```bash
# Prérequis : Farm avec CultureProfile (pH: 5.5-6.5, EC: 1.2-2.0, Temp: 18-24)

curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 6.0,
    "ec": 1.5,
    "waterTemp": 20.0
  }'

# Résultat : Mesure créée, 0 alerte
```

### Scénario 2 : pH hors plage (1 alerte)

```bash
curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 7.5,
    "ec": 1.5,
    "waterTemp": 20.0
  }'

# Résultat : Mesure créée + 1 alerte PH_OUT_OF_RANGE (WARN)
```

### Scénario 3 : Multiples anomalies (3 alertes)

```bash
curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 8.5,
    "ec": 3.0,
    "waterTemp": 28.0
  }'

# Résultat : Mesure créée + 3 alertes :
# - PH_OUT_OF_RANGE (CRITICAL)
# - EC_OUT_OF_RANGE (WARN)
# - TEMP_OUT_OF_RANGE (WARN)
```

### Vérification des alertes

```bash
# Toutes les alertes
curl http://localhost/api/alerts \
  -H "Authorization: Bearer YOUR_TOKEN"

# Alertes non résolues uniquement
curl "http://localhost/api/alerts?resolved=false" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Alertes critiques
curl "http://localhost/api/alerts?severity=CRITICAL" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Alertes pour un réservoir spécifique
curl "http://localhost/api/alerts?reservoir=/api/reservoirs/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Résolution d'une alerte

```bash
curl -X PATCH http://localhost/api/alerts/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/merge-patch+json" \
  -d '{
    "resolvedAt": "2025-11-20T14:30:00Z"
  }'
```

## Commandes utiles

### Consulter les alertes en base

```bash
php bin/console doctrine:query:sql "SELECT * FROM alert ORDER BY created_at DESC"
```

### Statistiques des alertes

```bash
php bin/console doctrine:query:sql "
  SELECT 
    type, 
    severity, 
    COUNT(*) as count 
  FROM alert 
  WHERE resolved_at IS NULL 
  GROUP BY type, severity
"
```

### Supprimer les anciennes alertes résolues

```bash
php bin/console doctrine:query:sql "
  DELETE FROM alert 
  WHERE resolved_at IS NOT NULL 
    AND resolved_at < datetime('now', '-30 days')
"
```

## Logs

Le service `AnomalyDetector` génère des logs pour chaque détection :

- **INFO** : Pas de CultureProfile configuré
- **INFO** : Nombre d'alertes générées
- **WARNING** : Détails de chaque anomalie détectée

**Exemple de logs** :
```
[2025-11-20 12:00:15] app.WARNING: pH anomaly detected {"measurement_id":42,"ph_measured":7.5,"ph_min":5.5,"ph_max":6.5}
[2025-11-20 12:00:15] app.INFO: Anomaly detection completed {"measurement_id":42,"alerts_count":1}
```

## Évolutions futures (V2)

1. **Notifications** :
   - Email automatique pour alertes CRITICAL
   - Notifications push mobile
   - Webhooks configurables

2. **Seuils personnalisés** :
   - Permettre de définir des seuils par réservoir
   - Surcharge du CultureProfile au niveau Farm ou Reservoir

3. **Tendances** :
   - Détection de tendances (valeurs qui se dégradent progressivement)
   - Prédiction d'anomalies futures

4. **Actions correctives** :
   - Suggestions automatiques d'actions
   - Intégration avec systèmes d'automatisation

5. **Dashboard** :
   - Vue d'ensemble des alertes actives
   - Graphiques et statistiques

## Acceptance Criteria ✅

- [x] Une mesure hors plage génère une alerte automatiquement
- [x] Plusieurs anomalies génèrent plusieurs alertes distinctes
- [x] Aucune alerte n'est créée si la mesure est dans les normes
- [x] `GET /api/alerts` retourne les alertes de l'utilisateur triées par `createdAt DESC`
- [x] Sécurité : un utilisateur ne peut voir que ses propres alertes
- [x] Filtrage automatique par AlertQueryExtension
- [x] Types d'alertes : PH_OUT_OF_RANGE, EC_OUT_OF_RANGE, TEMP_OUT_OF_RANGE
- [x] Niveaux de sévérité : INFO, WARN, CRITICAL
- [x] Possibilité de marquer une alerte comme résolue (PATCH)
- [x] Documentation complète avec docstrings

## Conformité OpenAPI

La spécification OpenAPI est générée automatiquement par API Platform et comprend :

- Tous les endpoints Alert (GET, PATCH)
- Schémas de requête/réponse
- Filtres disponibles
- Règles de sécurité

**Accès à la documentation** :
```
http://localhost/api/docs
```

## Fichiers créés/modifiés

### Créés
- `src/Entity/Alert.php`
- `src/Repository/AlertRepository.php`
- `src/Service/AnomalyDetector.php`
- `src/Extension/AlertQueryExtension.php`
- `migrations/Version20251120113530.php`
- `backend/docs/EPIC-2-ALERT-IMPLEMENTATION.md` (ce fichier)

### Modifiés
- `src/Entity/Farm.php` - Ajout relation `cultureProfile`
- `src/Entity/Reservoir.php` - Ajout relation `alerts`
- `src/State/MeasurementPostProcessor.php` - Intégration AnomalyDetector

## Résumé

Le système d'alertes automatiques est maintenant **opérationnel et prêt pour la production**. Il détecte automatiquement les anomalies lors de la création de mesures, génère des alertes avec des niveaux de sévérité appropriés, et respecte toutes les contraintes de sécurité.

Pour activer le système :
1. Configurer un `CultureProfile` pour chaque ferme
2. Créer des mesures via l'API
3. Consulter les alertes générées sur `/api/alerts`
