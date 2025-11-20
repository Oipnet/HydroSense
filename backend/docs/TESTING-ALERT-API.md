# Guide de test - Système d'alertes

## Étape 1 : Vérifier les CultureProfiles disponibles

```bash
curl http://localhost/api/culture_profiles \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Résultat attendu** : Liste des profils de culture (Laitue, Basilic, Fraises, etc.)

## Étape 2 : Configurer une ferme avec un CultureProfile

```bash
# Récupérer vos fermes
curl http://localhost/api/farms \
  -H "Authorization: Bearer YOUR_TOKEN"

# Configurer la ferme avec un profil de culture
curl -X PATCH http://localhost/api/farms/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/merge-patch+json" \
  -d '{
    "cultureProfile": "/api/culture_profiles/1"
  }'
```

**Résultat attendu** : Ferme mise à jour avec `cultureProfile: {...}`

## Étape 3 : Créer une mesure normale (pas d'alerte)

```bash
# Exemple avec des valeurs normales pour la Laitue (pH: 5.5-6.5, EC: 1.2-2.0, Temp: 18-24)
curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 6.0,
    "ec": 1.5,
    "waterTemp": 20.0,
    "measuredAt": "2025-11-20T12:00:00Z"
  }'
```

**Résultat attendu** : 
- Mesure créée avec succès (status 201)
- Aucune alerte générée

## Étape 4 : Créer une mesure avec pH hors plage (1 alerte)

```bash
curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 7.8,
    "ec": 1.5,
    "waterTemp": 20.0,
    "measuredAt": "2025-11-20T12:05:00Z"
  }'
```

**Résultat attendu** :
- Mesure créée avec succès
- 1 alerte `PH_OUT_OF_RANGE` générée automatiquement

## Étape 5 : Vérifier les alertes générées

```bash
# Toutes les alertes
curl http://localhost/api/alerts \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Résultat attendu** :
```json
{
  "hydra:member": [
    {
      "@id": "/api/alerts/1",
      "@type": "Alert",
      "id": 1,
      "type": "PH_OUT_OF_RANGE",
      "severity": "WARN",
      "message": "pH level 7.80 is outside the recommended range [5.50 - 6.50] for Laitue",
      "measuredValue": 7.8,
      "expectedMin": 5.5,
      "expectedMax": 6.5,
      "createdAt": "2025-11-20T12:05:00+00:00",
      "resolvedAt": null,
      "resolved": false
    }
  ],
  "hydra:totalItems": 1
}
```

## Étape 6 : Filtrer les alertes non résolues

```bash
curl "http://localhost/api/alerts?resolved=false" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Résultat attendu** : Uniquement les alertes avec `resolvedAt: null`

## Étape 7 : Créer une mesure avec multiples anomalies

```bash
curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 8.5,
    "ec": 3.2,
    "waterTemp": 28.0,
    "measuredAt": "2025-11-20T12:10:00Z"
  }'
```

**Résultat attendu** :
- Mesure créée
- 3 alertes générées :
  - `PH_OUT_OF_RANGE` (CRITICAL - déviation > 25%)
  - `EC_OUT_OF_RANGE` (CRITICAL - déviation > 25%)
  - `TEMP_OUT_OF_RANGE` (WARN - déviation 10-25%)

## Étape 8 : Filtrer par sévérité

```bash
# Alertes critiques uniquement
curl "http://localhost/api/alerts?severity=CRITICAL" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Résultat attendu** : Les 2 alertes CRITICAL du test précédent

## Étape 9 : Marquer une alerte comme résolue

```bash
curl -X PATCH http://localhost/api/alerts/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/merge-patch+json" \
  -d '{
    "resolvedAt": "2025-11-20T14:30:00Z"
  }'
```

**Résultat attendu** : Alerte mise à jour avec `resolved: true`

## Étape 10 : Vérifier la sécurité

```bash
# Tenter d'accéder à une alerte d'un autre utilisateur
curl http://localhost/api/alerts/999 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Résultat attendu** : 
- 404 Not Found (si l'alerte appartient à un autre utilisateur)
- 403 Forbidden (selon la configuration de sécurité)

## Test via la documentation OpenAPI

1. Ouvrir http://localhost/api/docs
2. Naviguer vers `/api/alerts`
3. Tester les différents endpoints interactivement
4. Vérifier les filtres et la pagination

## Vérification en base de données

```bash
# Depuis le terminal backend/
php bin/console doctrine:query:sql "SELECT * FROM alert ORDER BY created_at DESC LIMIT 10"
```

**Résultat attendu** : Liste des 10 dernières alertes créées

## Test des logs

```bash
# Consulter les logs de l'application
tail -f var/log/dev.log | grep -i "anomaly"
```

**Résultat attendu** : Logs de détection d'anomalies lors de la création de mesures

## Checklist de validation

- [ ] Les alertes sont créées automatiquement lors de la création de mesures hors plage
- [ ] Plusieurs anomalies dans une même mesure génèrent plusieurs alertes distinctes
- [ ] Aucune alerte n'est créée pour des mesures normales
- [ ] Les alertes sont triées par `createdAt DESC` par défaut
- [ ] Les filtres fonctionnent (type, severity, resolved, reservoir)
- [ ] Un utilisateur ne peut voir que ses propres alertes
- [ ] La sévérité est calculée correctement (INFO < 10%, WARN 10-25%, CRITICAL > 25%)
- [ ] Les alertes peuvent être marquées comme résolues
- [ ] Les messages d'alerte sont descriptifs et incluent les valeurs
- [ ] La documentation OpenAPI est accessible et complète

## En cas de problème

### Pas d'alerte générée malgré une valeur hors plage

**Vérifier** :
1. La ferme a-t-elle un `cultureProfile` configuré ?
   ```bash
   curl http://localhost/api/farms/1 -H "Authorization: Bearer YOUR_TOKEN"
   ```
2. Les logs indiquent-ils "No CultureProfile configured" ?
   ```bash
   tail -f var/log/dev.log | grep "No CultureProfile"
   ```

**Solution** : Configurer un CultureProfile sur la ferme (voir Étape 2)

### Erreur 500 lors de la création d'une mesure

**Vérifier** :
1. Les logs d'erreur :
   ```bash
   tail -f var/log/dev.log
   ```
2. La relation Farm → CultureProfile existe-t-elle ?
   ```bash
   php bin/console doctrine:schema:validate
   ```

**Solution** : Relancer la migration si nécessaire

### Alertes d'autres utilisateurs visibles

**Vérifier** :
1. Le service AlertQueryExtension est-il enregistré ?
   ```bash
   php bin/console debug:container AlertQueryExtension
   ```

**Solution** : Vider le cache
```bash
php bin/console cache:clear
```

## Commandes de débogage utiles

```bash
# Lister tous les services d'extension
php bin/console debug:container --tag=api_platform.doctrine.orm.query_extension.collection

# Vérifier la configuration API Platform
php bin/console debug:config api_platform

# Valider le schéma de base de données
php bin/console doctrine:schema:validate

# Voir les routes API disponibles
php bin/console debug:router | grep alert
```

## Conclusion

Si tous les tests passent, le système d'alertes est **opérationnel** ! 🎉

Pour toute question, consulter :
- Documentation complète : `backend/docs/EPIC-2-ALERT-IMPLEMENTATION.md`
- API docs : http://localhost/api/docs
