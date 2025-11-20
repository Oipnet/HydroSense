# Quick Start - Création des Issues HydroSense

## 🎯 Objectif
Créer automatiquement les 24 issues GitHub pour démarrer le projet HydroSense.

## ⚡ Méthode Rapide (gh CLI)

### Prérequis
- gh CLI installé : https://cli.github.com/

### Commandes
```bash
# S'authentifier
gh auth login

# Créer toutes les issues
./create-issues.sh
```

## 🔧 Méthodes Alternatives

### Node.js
```bash
export GITHUB_TOKEN=your_token_here
node create-issues.js
```

### Python
```bash
export GITHUB_TOKEN=your_token_here
python3 create-issues.py
```

## 📝 Obtenir un Token GitHub

1. Aller sur : https://github.com/settings/tokens
2. "Generate new token (classic)"
3. Nom : "HydroSense Issues"
4. Scope : Cocher `repo`
5. Générer et copier le token

## ✅ Vérification

Après exécution :
- 24 issues créées ✓
- 9 labels créés ✓
- Issues organisées par EPIC ✓

Voir : https://github.com/Oipnet/HydroSense/issues

## 📚 Documentation Complète

Pour plus de détails, consultez [ISSUES_CREATION_GUIDE.md](./ISSUES_CREATION_GUIDE.md)

## 🎨 Labels Créés

| Label | Description | Couleur |
|-------|-------------|---------|
| epic:setup | EPIC 1 - Setup Monorepo | 🟢 Vert |
| epic:backend | EPIC 2 - Backend | 🔵 Bleu |
| epic:frontend | EPIC 3 - Frontend | 🟡 Jaune |
| epic:infra | EPIC 4 - Infrastructure | 🔴 Rouge |
| epic:ia | EPIC 5 - IA & Doc | 🟣 Violet |
| backend | Technique - Backend | 🔷 Bleu clair |
| frontend | Technique - Frontend | 🟠 Rose |
| infra | Technique - Infra | 🟥 Rouge clair |
| ia | Technique - IA | 🔹 Bleu pâle |

## 🏗️ Structure du Projet (après EPIC-1)

```
HydroSense/
├── backend/          # Symfony 7 + API Platform
├── frontend/         # Nuxt 3
├── infra/           # Docker & déploiement
├── .gitignore
└── README.md
```

## 🚀 Ordre de Développement

1. **EPIC-1** : Setup Monorepo (Issue #1)
2. **EPIC-2** : Backend (Issues #2-11)
3. **EPIC-3** : Frontend (Issues #12-20)
4. **EPIC-4** : Infrastructure (Issues #21-22)
5. **EPIC-5** : IA & Documentation (Issues #23-24)

## 💡 Conseils

- Suivre l'ordre des EPICs
- Une branche par issue recommandée
- Référencer l'issue dans chaque commit
- Marquer les tâches complétées dans l'issue

## ❓ Problème ?

Consultez la section "Dépannage" dans [ISSUES_CREATION_GUIDE.md](./ISSUES_CREATION_GUIDE.md#-dépannage)
