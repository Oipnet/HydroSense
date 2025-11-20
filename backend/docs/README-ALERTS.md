# Système d'Alertes Automatiques HydroSense

## 🚀 Démarrage rapide

Le système d'alertes détecte automatiquement les anomalies dans vos mesures hydroponiques et vous alerte en temps réel.

### Configuration minimale

1. **Configurer un profil de culture pour votre ferme** :
```bash
PATCH /api/farms/{id}
{
  "cultureProfile": "/api/culture_profiles/1"
}
```

2. **Créer des mesures normalement** :
```bash
POST /api/measurements
{
  "reservoir": "/api/reservoirs/1",
  "ph": 6.0,
  "ec": 1.5,
  "waterTemp": 20.0
}
```

3. **Consulter vos alertes** :
```bash
GET /api/alerts
GET /api/alerts?resolved=false  # Seulement les non résolues
GET /api/alerts?severity=CRITICAL  # Seulement les critiques
```

C'est tout ! Le système fonctionne automatiquement. 🎉

---

## 📚 Documentation

### Pour les développeurs

- **[EPIC-2-ALERT-IMPLEMENTATION.md](docs/EPIC-2-ALERT-IMPLEMENTATION.md)** - Documentation technique complète
  - Architecture détaillée
  - Schéma de données
  - Workflow de détection
  - Calcul de sévérité
  - Évolutions futures

### Pour les testeurs

- **[TESTING-ALERT-API.md](docs/TESTING-ALERT-API.md)** - Guide de test étape par étape
  - 10 scénarios de test
  - Commandes curl prêtes à l'emploi
  - Checklist de validation
  - Dépannage

### Pour les gestionnaires de projet

- **[EPIC-2-ALERT-SYSTEM-SUMMARY.md](EPIC-2-ALERT-SYSTEM-SUMMARY.md)** - Résumé exécutif
  - Tâches accomplies
  - Acceptance criteria validés
  - Fichiers créés/modifiés
  - Statut final

---

## 🎯 Fonctionnalités

### Détection automatique

- ✅ **pH hors plage** → Alerte PH_OUT_OF_RANGE
- ✅ **EC hors plage** → Alerte EC_OUT_OF_RANGE  
- ✅ **Température hors plage** → Alerte TEMP_OUT_OF_RANGE

### Sévérité intelligente

- 🔵 **INFO** : Déviation < 10% (légère)
- 🟡 **WARN** : Déviation 10-25% (modérée)
- 🔴 **CRITICAL** : Déviation > 25% (sévère)

### Gestion des alertes

- Consulter toutes vos alertes
- Filtrer par type, sévérité, statut
- Marquer comme résolues
- Historique complet avec mesures associées

---

## 🔍 Endpoints API

### Consultation

```http
GET /api/alerts                                    # Toutes les alertes
GET /api/alerts/{id}                               # Une alerte spécifique
GET /api/alerts?resolved=false                     # Non résolues
GET /api/alerts?severity=CRITICAL                  # Critiques uniquement
GET /api/alerts?type=PH_OUT_OF_RANGE              # Par type
GET /api/alerts?reservoir=/api/reservoirs/1       # Par réservoir
GET /api/alerts?createdAt[after]=2025-11-20       # Par date
```

### Résolution

```http
PATCH /api/alerts/{id}
{
  "resolvedAt": "2025-11-20T14:30:00Z"
}
```

---

## 🔒 Sécurité

### Isolation des données

Chaque utilisateur ne voit **que ses propres alertes** via :
- Filtrage automatique par propriétaire
- Expressions de sécurité API Platform
- Extension Doctrine personnalisée

### Cascade de suppression

Les alertes sont automatiquement supprimées si :
- Le réservoir associé est supprimé
- La mesure associée est supprimée

---

## 💡 Exemples concrets

### Exemple 1 : Culture de laitue

**Configuration** :
- pH optimal : 5.5 - 6.5
- EC optimale : 1.2 - 2.0 mS/cm
- Température : 18 - 24°C

**Mesure** : pH = 7.8, EC = 1.5, Temp = 20°C

**Résultat** : 
```json
{
  "type": "PH_OUT_OF_RANGE",
  "severity": "CRITICAL",
  "message": "pH level 7.80 is outside the recommended range [5.50 - 6.50] for Laitue",
  "measuredValue": 7.8,
  "expectedMin": 5.5,
  "expectedMax": 6.5
}
```

### Exemple 2 : Multiples anomalies

**Mesure** : pH = 8.5, EC = 3.2, Temp = 28°C

**Résultat** : **3 alertes générées** :
1. PH_OUT_OF_RANGE (CRITICAL)
2. EC_OUT_OF_RANGE (CRITICAL)
3. TEMP_OUT_OF_RANGE (WARN)

---

## 🛠️ Commandes utiles

### Développement

```bash
# Valider le schéma DB
php bin/console doctrine:schema:validate

# Lister les routes Alert
php bin/console debug:router | grep alert

# Vérifier le service AnomalyDetector
php bin/console debug:container AnomalyDetector

# Consulter les logs de détection
tail -f var/log/dev.log | grep -i "anomaly"
```

### Production

```bash
# Compter les alertes non résolues
php bin/console doctrine:query:sql "
  SELECT COUNT(*) FROM alert WHERE resolved_at IS NULL
"

# Supprimer les anciennes alertes résolues (> 30 jours)
php bin/console doctrine:query:sql "
  DELETE FROM alert 
  WHERE resolved_at < datetime('now', '-30 days')
"

# Statistiques par type et sévérité
php bin/console doctrine:query:sql "
  SELECT type, severity, COUNT(*) as count 
  FROM alert 
  GROUP BY type, severity
"
```

---

## 📊 Architecture technique

```
Measurement créée
        ↓
MeasurementPostProcessor
        ↓
AnomalyDetector.detect()
        ↓
Comparaison avec CultureProfile
        ↓
Alert(s) générée(s) et persistée(s)
        ↓
Visible via GET /api/alerts
```

### Composants

| Composant | Rôle | Fichier |
|-----------|------|---------|
| **Alert** | Entité | `src/Entity/Alert.php` |
| **AnomalyDetector** | Détection | `src/Service/AnomalyDetector.php` |
| **MeasurementPostProcessor** | Intégration | `src/State/MeasurementPostProcessor.php` |
| **AlertQueryExtension** | Sécurité | `src/Extension/AlertQueryExtension.php` |
| **AlertRepository** | Requêtes | `src/Repository/AlertRepository.php` |

---

## 🧪 Tests

### Test rapide (2 minutes)

```bash
# 1. Configurer une ferme
curl -X PATCH http://localhost/api/farms/1 \
  -H "Authorization: Bearer TOKEN" \
  -d '{"cultureProfile": "/api/culture_profiles/1"}'

# 2. Créer une mesure hors plage
curl -X POST http://localhost/api/measurements \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "reservoir": "/api/reservoirs/1",
    "ph": 8.5
  }'

# 3. Vérifier l'alerte
curl http://localhost/api/alerts -H "Authorization: Bearer TOKEN"
```

### Tests complets

Voir **[TESTING-ALERT-API.md](docs/TESTING-ALERT-API.md)** pour :
- 10 scénarios de test détaillés
- Tests de sécurité
- Tests de filtrage
- Tests de résolution

---

## 🎓 FAQ

### Q : Dois-je configurer quelque chose pour activer les alertes ?

**R** : Oui, vous devez associer un `CultureProfile` à votre `Farm`. Sans cela, aucune alerte ne sera générée (car on ne connaît pas les plages acceptables).

### Q : Que se passe-t-il si je ne fournis qu'une partie des valeurs (ex: seulement pH) ?

**R** : Le système vérifie uniquement les valeurs fournies. Si vous ne mesurez pas l'EC, aucune alerte EC ne sera générée.

### Q : Les alertes sont-elles supprimées automatiquement ?

**R** : Non, elles sont persistées en base. Vous pouvez les marquer comme résolues ou les supprimer via un script de nettoyage (voir commandes utiles).

### Q : Puis-je personnaliser les seuils par réservoir ?

**R** : Pas dans la V1. Actuellement, les seuils sont définis au niveau du `CultureProfile`. La V2 permettra des seuils personnalisés par réservoir ou ferme.

### Q : Comment puis-je être notifié des alertes CRITICAL ?

**R** : Dans la V1, vous devez consulter `/api/alerts`. La V2 ajoutera des notifications email/SMS/push automatiques.

### Q : Les alertes fonctionnent-elles avec l'import CSV ?

**R** : Oui ! Le système détecte automatiquement les anomalies lors de l'import CSV massif. Toutes les mesures hors plage généreront des alertes.

---

## 📈 Évolutions prévues (V2)

### Court terme
- [ ] Notifications email pour alertes CRITICAL
- [ ] Dashboard temps réel
- [ ] Export CSV des alertes

### Moyen terme
- [ ] Seuils personnalisables par réservoir
- [ ] Détection de tendances
- [ ] Suggestions d'actions correctives

### Long terme
- [ ] Prédiction d'anomalies (ML)
- [ ] Intégration systèmes d'automatisation
- [ ] Alertes contextuelles (météo, saison, etc.)

---

## 🆘 Support

### Documentation
- **Technique** : [EPIC-2-ALERT-IMPLEMENTATION.md](docs/EPIC-2-ALERT-IMPLEMENTATION.md)
- **Tests** : [TESTING-ALERT-API.md](docs/TESTING-ALERT-API.md)
- **API** : http://localhost/api/docs

### Logs
```bash
# Logs de détection
tail -f var/log/dev.log | grep "anomaly"

# Logs d'erreur
tail -f var/log/dev.log | grep "ERROR"
```

### Problèmes courants
1. **Pas d'alerte générée** → Vérifier que la Farm a un CultureProfile
2. **Erreur 500** → Consulter les logs
3. **Alertes d'autres users visibles** → Vider le cache

---

## ✅ Statut

**Version** : 1.0.0  
**Date** : 20 novembre 2025  
**Statut** : ✅ Production Ready  
**Tests** : ✅ Validés  
**Documentation** : ✅ Complète  

---

**🎉 Le système d'alertes est opérationnel !**

Pour démarrer, consultez le **[Guide de test](docs/TESTING-ALERT-API.md)** 📖
