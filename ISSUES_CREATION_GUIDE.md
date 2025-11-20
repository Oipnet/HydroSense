# Guide de Création des Issues HydroSense

Ce document explique comment créer les 24 issues GitHub pour le projet HydroSense.

## 📋 Vue d'ensemble

Le projet HydroSense nécessite la création de **24 issues** réparties en **5 EPICs** :

- **EPIC-1 : Setup Monorepo** - 1 issue
- **EPIC-2 : Backend** - 10 issues (2-11)
- **EPIC-3 : Frontend** - 9 issues (12-20)
- **EPIC-4 : Infra** - 2 issues (21-22)
- **EPIC-5 : IA** - 2 issues (23-24)

## 🎯 Structure des Issues

Chaque issue respecte le format suivant :

### Titre
Format : `[EPIC-X] Nom de la tâche`

Exemple : `[EPIC-2] User + Authentification JWT`

### Labels
Chaque issue a deux types de labels :
1. **Label d'EPIC** : `epic:setup`, `epic:backend`, `epic:frontend`, `epic:infra`, ou `epic:ia`
2. **Label technique** : `backend`, `frontend`, `infra`, ou `ia`

### Corps de l'issue
Structure markdown avec :
- **Description** : Courte description de la tâche
- **Objectif** : But principal de l'issue
- **Tâches** : Liste de tâches à cocher
- **Acceptance criteria** : Critères de validation

## 🚀 Méthodes de Création

Trois méthodes sont disponibles pour créer les issues :

### Méthode 1 : Script Shell avec gh CLI (Recommandé)

**Prérequis :**
- `gh` CLI installé ([Installation](https://cli.github.com/))
- Authentification GitHub active

**Commandes :**
```bash
# S'authentifier si nécessaire
gh auth login

# Exécuter le script
./create-issues.sh
```

**Avantages :**
- Simple et rapide
- Pas besoin de token explicite
- Gestion automatique des labels

### Méthode 2 : Script Node.js avec GitHub API

**Prérequis :**
- Node.js installé
- Token GitHub avec permissions `repo`

**Création du token :**
1. Aller sur GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Cliquer "Generate new token (classic)"
3. Nom : "HydroSense Issues Creator"
4. Scopes : Cocher `repo` (full control of private repositories)
5. Générer et copier le token

**Commandes :**
```bash
# Définir le token (remplacer YOUR_TOKEN)
export GITHUB_TOKEN=your_github_token_here

# Exécuter le script
node create-issues.js
```

**Avantages :**
- Fonctionne sur toutes les plateformes
- Pas de dépendance externe (seulement Node.js)

### Méthode 3 : Création Manuelle

Si les scripts automatiques ne fonctionnent pas, vous pouvez créer les issues manuellement en utilisant le fichier `issues-data.json` comme référence.

**Étapes :**
1. Ouvrir `issues-data.json`
2. Pour chaque issue :
   - Créer une nouvelle issue sur GitHub
   - Copier le titre
   - Copier le corps
   - Ajouter les labels (créer les labels si nécessaire)

## 📊 Labels à Créer

Les scripts créent automatiquement les labels suivants avec leurs couleurs :

| Label | Couleur | Type |
|-------|---------|------|
| `epic:setup` | `#0E8A16` (Vert) | EPIC |
| `epic:backend` | `#1D76DB` (Bleu) | EPIC |
| `epic:frontend` | `#FBCA04` (Jaune) | EPIC |
| `epic:infra` | `#D93F0B` (Rouge) | EPIC |
| `epic:ia` | `#8B4789` (Violet) | EPIC |
| `backend` | `#0075CA` (Bleu clair) | Technique |
| `frontend` | `#F9D0C4` (Rose) | Technique |
| `infra` | `#E99695` (Rouge clair) | Technique |
| `ia` | `#C5DEF5` (Bleu pâle) | Technique |

## 🔍 Vérification

Après la création, vérifiez que :
- ✅ Les 24 issues sont créées
- ✅ Chaque issue a le bon préfixe `[EPIC-X]`
- ✅ Les labels sont correctement appliqués
- ✅ Le corps des issues contient les sections Objectif, Tâches et Acceptance criteria

**URL des issues :**
https://github.com/Oipnet/HydroSense/issues

## 📝 Détail des Issues

### EPIC-1 : Setup Monorepo (1 issue)

1. **[EPIC-1] Initialiser le monorepo**
   - Labels : `epic:setup`, `infra`
   - Structure de base avec dossiers backend, frontend, infra

### EPIC-2 : Backend (10 issues)

2. **[EPIC-2] Initialiser backend Symfony + API Platform**
   - Labels : `epic:backend`, `backend`
3. **[EPIC-2] User + Authentification JWT**
   - Labels : `epic:backend`, `backend`
4. **[EPIC-2] Entités Farm & Reservoir**
   - Labels : `epic:backend`, `backend`
5. **[EPIC-2] Entité CultureProfile (référentiel)**
   - Labels : `epic:backend`, `backend`
6. **[EPIC-2] Entité Measurement (mesures pH/EC/temp)**
   - Labels : `epic:backend`, `backend`
7. **[EPIC-2] Import CSV des mesures**
   - Labels : `epic:backend`, `backend`
8. **[EPIC-2] Entité Alert + moteur d'analyse simple**
   - Labels : `epic:backend`, `backend`
9. **[EPIC-2] Entité JournalEntry (journal de culture)**
   - Labels : `epic:backend`, `backend`
10. **[EPIC-2] Endpoint Dashboard (vue synthèse backend)**
    - Labels : `epic:backend`, `backend`
11. **[EPIC-2] OpenAPI propre et documenté**
    - Labels : `epic:backend`, `backend`, `ia`

### EPIC-3 : Frontend (9 issues)

12. **[EPIC-3] Initialiser Nuxt 3 + Tailwind + Pinia**
    - Labels : `epic:frontend`, `frontend`
13. **[EPIC-3] Générer le client API depuis OpenAPI**
    - Labels : `epic:frontend`, `frontend`
14. **[EPIC-3] Auth (login + middleware)**
    - Labels : `epic:frontend`, `frontend`
15. **[EPIC-3] Page Liste des Réservoirs**
    - Labels : `epic:frontend`, `frontend`
16. **[EPIC-3] Page Détail d'un Réservoir**
    - Labels : `epic:frontend`, `frontend`
17. **[EPIC-3] Onglet Mesures**
    - Labels : `epic:frontend`, `frontend`
18. **[EPIC-3] Onglet Alerts**
    - Labels : `epic:frontend`, `frontend`
19. **[EPIC-3] Onglet Journal**
    - Labels : `epic:frontend`, `frontend`
20. **[EPIC-3] Dashboard global frontend**
    - Labels : `epic:frontend`, `frontend`

### EPIC-4 : Infra (2 issues)

21. **[EPIC-4] Docker Compose backend + Postgres**
    - Labels : `epic:infra`, `infra`
22. **[EPIC-4] Dockerfile de build Nuxt 3 (production)**
    - Labels : `epic:infra`, `infra`

### EPIC-5 : IA (2 issues)

23. **[EPIC-5] Améliorer descriptions OpenAPI pour usage IA**
    - Labels : `epic:ia`, `ia`, `backend`
24. **[EPIC-5] Ajouter docstrings sur Processors & Providers**
    - Labels : `epic:ia`, `ia`, `backend`

## 🐛 Dépannage

### Problème : gh CLI pas authentifié
**Solution :**
```bash
gh auth login
```
Suivre les instructions à l'écran.

### Problème : Permission denied sur les scripts
**Solution :**
```bash
chmod +x create-issues.sh create-issues.js
```

### Problème : Rate limiting GitHub API
**Solution :**
Les scripts incluent des délais entre les requêtes. Si vous rencontrez quand même des problèmes, attendez quelques minutes et relancez.

### Problème : Label déjà existant avec une couleur différente
**Solution :**
Les scripts détectent les labels existants et ne les recréent pas. Si vous voulez changer les couleurs, supprimez d'abord les labels manuellement sur GitHub.

## 📞 Support

Pour toute question ou problème :
1. Vérifier que vous avez les permissions nécessaires sur le repository
2. Vérifier que gh CLI ou Node.js sont correctement installés
3. Consulter les logs d'erreur des scripts

## ✨ Fichiers Fournis

- `issues-data.json` : Données structurées de toutes les issues
- `create-issues.sh` : Script shell utilisant gh CLI
- `create-issues.js` : Script Node.js utilisant l'API GitHub
- `ISSUES_CREATION_GUIDE.md` : Ce guide

## 🎉 Après la Création

Une fois les issues créées :
1. Vérifier sur https://github.com/Oipnet/HydroSense/issues
2. Trier par labels pour voir les EPICs
3. Commencer par EPIC-1 (Setup Monorepo)
4. Suivre l'ordre des issues pour chaque EPIC
