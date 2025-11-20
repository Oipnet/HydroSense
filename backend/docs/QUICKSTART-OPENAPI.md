# Issue #14 - OpenAPI Export - Quick Reference

## 🚀 Commande d'export

```bash
php bin/console api:openapi:export --output=public/openapi.json
```

## 📍 Accès au fichier

### Local

```
http://localhost:8000/openapi.json
```

### Production

```
https://votre-domaine.com/openapi.json
```

## 🔧 Génération de client Nuxt

```bash
# Installation du générateur (si nécessaire)
npm install -g @api-platform/client-generator

# Génération du client Nuxt
npx @api-platform/client-generator \
  http://localhost:8000/openapi.json \
  --generator nuxt \
  --output ../frontend/
```

## 📖 Documentation interactive

```
http://localhost:8000/api/docs
```

## ✅ Vérification rapide

### Tester la génération

```bash
php bin/console api:openapi:export
```

### Vérifier la présence des descriptions

```powershell
Select-String -Path public/openapi.json -Pattern "Retrieve all farms owned by"
```

### Voir la structure JSON

```powershell
Get-Content public/openapi.json | ConvertFrom-Json | Select-Object openapi,info
```

## 📊 Statistiques du fichier généré

-   **Format** : OpenAPI 3.1.0
-   **Taille** : ~150+ Ko
-   **Ressources** : 6 principales (Farm, Reservoir, Measurement, Alert, JournalEntry, Dashboard)
-   **Endpoints** : ~25 opérations documentées
-   **Schémas** : ~30 schémas de données

## 🎯 Ressources documentées

| Ressource    | Endpoint               | Description                                                                |
| ------------ | ---------------------- | -------------------------------------------------------------------------- |
| Farm         | `/api/farms`           | Gestion des fermes avec filtrage automatique par propriétaire              |
| Reservoir    | `/api/reservoirs`      | Gestion des bacs avec mesures et journal                                   |
| Measurement  | `/api/measurements`    | Enregistrement des mesures (pH, EC, température) avec génération d'alertes |
| Alert        | `/api/alerts`          | Consultation et résolution des alertes                                     |
| JournalEntry | `/api/journal_entries` | Notes de culture avec photos optionnelles                                  |
| Dashboard    | `/api/dashboard`       | Vue d'ensemble avec statuts calculés                                       |

## 🔍 Exemples de descriptions

### Measurement - POST

```
"Record a new measurement for a reservoir.
Alerts will be automatically generated if values fall outside
acceptable ranges defined in the culture profile."
```

### Alert - GET Collection

```
"Retrieve all alerts for reservoirs owned by the authenticated user.
Use filters: ?resolved=false, ?severity=CRITICAL,
?type=PH_OUT_OF_RANGE, ?createdAt[after]=2025-01-01"
```

## 🛠️ Workflow de mise à jour

1. **Modifier le code** (ajouter/modifier des entités ou opérations)
2. **Vider le cache** : `php bin/console cache:clear`
3. **Régénérer OpenAPI** : `php bin/console api:openapi:export --output=public/openapi.json`
4. **Commit** : `git add public/openapi.json && git commit -m "Update OpenAPI spec"`

## 💡 Conseils

### Pour les IA

-   Les descriptions incluent le contexte métier (plages optimales, unités de mesure)
-   Les filtres disponibles sont documentés dans les descriptions
-   Les règles de sécurité (ownership automatique) sont explicites

### Pour les développeurs

-   Documentation synchronisée avec le code
-   Pas de maintenance de documentation externe
-   Génération automatique de clients TypeScript

### Pour la production

-   Exposer le fichier via CDN pour accès rapide
-   Versionner le fichier OpenAPI dans Git
-   Automatiser la génération dans le pipeline CI/CD

## 📚 Documentation complète

Voir `docs/ISSUE-14-OPENAPI-DOCUMENTATION.md` pour les détails techniques complets.

---

**Date** : 20 novembre 2025  
**Issue** : #14 - [EPIC-2] OpenAPI propre et documenté  
**Statut** : ✅ COMPLÉTÉ
