# Issue #15 - [EPIC-3] Initialiser Nuxt 4 + Tailwind + Pinia

## 📋 Plan d'implémentation

### Résumé

Mise en place complète du projet frontend Nuxt 4 avec TypeScript, Pinia et TailwindCSS dans le dossier `frontend/`.

---

## 🚀 Commandes d'installation

### Étape 1 : Vérifier les prérequis

```powershell
# Vérifier Node.js (doit être >= 18.0.0)
node --version

# Vérifier npm (doit être >= 9.0.0)
npm --version
```

### Étape 2 : Le projet Nuxt 4 existe déjà

Le projet a déjà été initialisé dans `frontend/`. Tous les fichiers de configuration ont été créés :

-   ✅ `nuxt.config.ts`
-   ✅ `package.json`
-   ✅ `tsconfig.json`
-   ✅ `tailwind.config.ts`
-   ✅ Structure de fichiers complète

### Étape 3 : Installer les dépendances

```powershell
# Se placer dans le dossier frontend
cd frontend

# Installer toutes les dépendances
npm install
```

Cette commande installera :

-   Nuxt 4 (`nuxt@^4.2.1`)
-   Pinia (`pinia@^3.0.4` + `@pinia/nuxt@^0.11.3`)
-   TailwindCSS (`@nuxtjs/tailwindcss@^6.14.0`)
-   TypeScript (`typescript@^5.9.3`)
-   Types et dépendances complémentaires

### Étape 4 : Configurer l'environnement

```powershell
# Copier le fichier d'exemple
cp .env.example .env

# Ou sur Windows PowerShell :
Copy-Item .env.example .env
```

Puis éditer `.env` :

```env
# API Backend URL
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000

# Application
NODE_ENV=development
```

### Étape 5 : Lancer le serveur de développement

```powershell
npm run dev
```

Le serveur démarre sur `http://localhost:3000`

---

## 📂 Fichiers créés

### Configuration

1. **`nuxt.config.ts`** - Configuration principale Nuxt 4

    - TypeScript strict activé
    - Modules Pinia et TailwindCSS
    - RuntimeConfig pour l'API (`NUXT_PUBLIC_API_BASE_URL`)
    - Configuration du CSS global

2. **`package.json`** - Dépendances et scripts

    - Nuxt 4.2.1
    - Pinia 3.0.4 + module Nuxt
    - TailwindCSS via module Nuxt
    - TypeScript 5.9.3
    - Scripts : dev, build, preview, typecheck

3. **`tsconfig.json`** - Configuration TypeScript

    - Mode strict activé
    - Toutes les options strictes TypeScript
    - Extension du tsconfig généré par Nuxt

4. **`tailwind.config.ts`** - Configuration TailwindCSS
    - Content paths configurés
    - Thème étendu avec couleurs HydroSense
    - Primary (bleu) et Secondary (vert)

### Styles

5. **`assets/css/main.css`** - CSS global
    - Directives Tailwind (@tailwind base, components, utilities)
    - Classes utilitaires custom (`.btn-primary`, `.btn-secondary`)
    - Styles de base pour body

### Composables

6. **`composables/useApi.ts`** - Wrapper $fetch pour l'API
    - Méthodes : `get()`, `post()`, `put()`, `patch()`, `delete()`
    - BaseURL automatique depuis `NUXT_PUBLIC_API_BASE_URL`
    - Types TypeScript

### Stores Pinia

7. **`stores/useUiStore.ts`** - Store UI global

    - State : sidebar, theme, notifications
    - Actions : toggle sidebar, set theme, manage notifications
    - Getters : sorted notifications, unread count

8. **`stores/useCounterStore.ts`** - Store exemple (démonstration)
    - Counter simple avec increment/decrement
    - Peut être supprimé après validation

### Layouts

9. **`layouts/default.vue`** - Layout par défaut
    - Header avec logo et navigation
    - Footer
    - Slot pour le contenu des pages
    - Bouton toggle theme

### Pages

10. **`pages/index.vue`** - Page d'accueil
    -   Affiche l'URL de l'API configurée
    -   Démo du store Counter (Pinia)
    -   Cards d'information sur la stack technique
    -   Liste des prochaines étapes
    -   Utilise TailwindCSS avec classes custom

### App

11. **`app.vue`** - Point d'entrée
    -   Utilise NuxtLayout et NuxtPage
    -   Configuration HTML lang="fr"

### Environnement

12. **`.env.example`** - Template variables d'environnement
13. **`.gitignore`** - Fichiers à exclure du repo

---

## ✅ Tests de validation

### 1. Vérifier que l'application démarre

```powershell
cd frontend
npm run dev
```

**Résultat attendu :**

```
  ➜ Local:   http://localhost:3000/
  ➜ Network: use --host to expose
```

### 2. Tester la page d'accueil

Ouvrir `http://localhost:3000` dans le navigateur.

**Vérifications :**

-   ✅ Header "HydroSense Frontend" visible
-   ✅ Badge vert "Configuration initiale réussie"
-   ✅ URL de l'API affichée (`http://localhost:8000`)
-   ✅ Counter Pinia fonctionnel (clic sur +/- fonctionne)
-   ✅ Styles TailwindCSS appliqués (couleurs, espacements, cards)

### 3. Tester Tailwind

**Vérifications visuelles :**

-   Les boutons ont des couleurs (bleu/vert)
-   Les cards ont des ombres (`shadow-md`)
-   Le texte est bien stylé (tailles, couleurs)
-   Les classes custom fonctionnent (`.btn-primary`, `.btn-secondary`)

### 4. Tester Pinia

Cliquer sur les boutons du compteur :

-   **Increment** : le compteur augmente
-   **Decrement** : le compteur diminue
-   **Reset** : le compteur revient à 0
-   Le "Double" se met à jour automatiquement

### 5. Tester la configuration API

**Vérification :**

-   L'URL `http://localhost:8000` est affichée dans la card "Configuration API"
-   Le status est "Configured" ou "Ready"

### 6. Tester TypeScript

```powershell
npm run typecheck
```

**Résultat attendu :**

```
✓ Type checking completed without errors
```

_(Note : Il peut y avoir des warnings temporaires sur les imports auto, ignorez-les si `npm run dev` fonctionne)_

---

## 🎨 Structure de la stack

### Nuxt 4

-   Framework Vue.js avec SSR/SSG
-   Auto-imports des composables
-   File-based routing
-   Modules ecosystem

### TypeScript

-   Mode strict activé
-   Types automatiques pour Vue et Nuxt
-   IntelliSense complet

### Pinia

-   Store management officiel pour Vue 3
-   API simple et intuitive
-   DevTools integration
-   TypeScript first

### TailwindCSS

-   Utility-first CSS framework
-   Configuration custom (couleurs HydroSense)
-   Classes utilitaires personnalisées
-   PurgeCSS automatique en production

---

## 🔌 Utilisation de l'API

### Exemple dans une page/composant

```vue
<script setup lang="ts">
const api = useApi();

// GET
const { data: farms } = await useAsyncData("farms", () =>
    api.get("/api/farms")
);

// POST
const createFarm = async (farmData: any) => {
    try {
        const newFarm = await api.post("/api/farms", farmData);
        console.log("Farm created:", newFarm);
    } catch (error) {
        console.error("Error:", error);
    }
};
</script>
```

---

## 📊 État d'avancement

### ✅ Complété

1. ✅ Projet Nuxt 4 initialisé
2. ✅ TypeScript configuré (mode strict)
3. ✅ Pinia installé et configuré
4. ✅ TailwindCSS installé et configuré
5. ✅ Layout par défaut créé
6. ✅ Page d'accueil de test créée
7. ✅ Composable `useApi()` créé
8. ✅ Stores Pinia exemple (UI + Counter)
9. ✅ Configuration env pour API base URL
10. ✅ Documentation complète (README.md)

### 🔜 Prochaines étapes (hors scope #15)

1. Générer le client OpenAPI depuis le backend
2. Créer les pages métier (`/dashboard`, `/farms`, etc.)
3. Implémenter l'authentification JWT
4. Créer les composants UI réutilisables
5. Ajouter les tests (Vitest)

---

## 🐛 Troubleshooting

### Erreur : "Cannot find module"

```powershell
# Supprimer node_modules et réinstaller
rm -rf node_modules .nuxt
npm install
```

### Port 3000 déjà utilisé

```powershell
# Utiliser un autre port
npm run dev -- --port 3001
```

### Types TypeScript non reconnus

```powershell
# Régénérer les types Nuxt
npm run postinstall
```

---

## 📝 Commit suggéré

```
feat: Initialize Nuxt 4 frontend with TypeScript, Pinia, and TailwindCSS (#15)

- Setup Nuxt 4 project structure in frontend/
- Configure TypeScript in strict mode
- Install and configure Pinia for state management
- Install and configure TailwindCSS with custom theme
- Create default layout with header and footer
- Add index page with demo and configuration display
- Create useApi() composable for API calls
- Setup environment variables for API base URL
- Add example stores (UI + Counter)

Tech Stack:
- Nuxt 4.2.1
- TypeScript 5.9.3
- Pinia 3.0.4
- TailwindCSS 3.x

Files created:
- nuxt.config.ts
- tailwind.config.ts
- composables/useApi.ts
- stores/useUiStore.ts, useCounterStore.ts
- layouts/default.vue
- pages/index.vue
- assets/css/main.css
- .env.example

Closes #15
```

---

**Date** : 20 novembre 2025  
**Issue** : #15 - [EPIC-3] Initialiser Nuxt 4 + Tailwind + Pinia  
**Statut** : ✅ PRÊT POUR COMMIT
