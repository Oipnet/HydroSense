# EPIC-3 - Issue #15 : Initialiser Nuxt 4 + Tailwind + Pinia

## ✅ Statut : TERMINÉ

## 📋 Résumé de l'implémentation

### Objectif

Mettre en place le projet frontend Nuxt 4 dans `frontend/` avec TypeScript, Pinia, TailwindCSS et configuration API.

---

## 🎯 Ce qui a été fait

### 1. Configuration Nuxt 4 ✅

-   ✅ `nuxt.config.ts` avec TypeScript strict
-   ✅ Modules Pinia et TailwindCSS
-   ✅ RuntimeConfig pour API (`NUXT_PUBLIC_API_BASE_URL`)
-   ✅ Configuration CSS global

### 2. TypeScript ✅

-   ✅ `tsconfig.json` en mode strict
-   ✅ Tous les flags de rigueur activés
-   ✅ Types auto-importés

### 3. Pinia (State Management) ✅

-   ✅ Module `@pinia/nuxt` configuré
-   ✅ Store `useUiStore` (sidebar, theme, notifications)
-   ✅ Store `useCounterStore` (exemple de démonstration)

### 4. TailwindCSS ✅

-   ✅ `tailwind.config.ts` avec thème custom
-   ✅ Couleurs HydroSense (primary bleu, secondary vert)
-   ✅ `assets/css/main.css` avec directives Tailwind
-   ✅ Classes utilitaires custom (`.btn-primary`, `.btn-secondary`)

### 5. Composable API ✅

-   ✅ `composables/useApi.ts`
-   ✅ Wrapper $fetch avec baseURL automatique
-   ✅ Méthodes : get, post, put, patch, delete

### 6. Layout & Pages ✅

-   ✅ `layouts/default.vue` avec header/footer
-   ✅ `pages/index.vue` page de test complète
-   ✅ Affichage config API et démo Pinia

### 7. Configuration ✅

-   ✅ `package.json` avec toutes les dépendances
-   ✅ `.env.example` pour les variables d'environnement
-   ✅ `.gitignore` adapté

### 8. Documentation ✅

-   ✅ `README.md` complet
-   ✅ `QUICKSTART.md` pour démarrage rapide
-   ✅ `docs/ISSUE-15-NUXT4-SETUP.md` guide détaillé

---

## 📦 Dépendances installées

```json
{
    "dependencies": {
        "nuxt": "^4.2.1",
        "pinia": "^3.0.4",
        "@pinia/nuxt": "^0.11.3",
        "vue": "^3.5.24"
    },
    "devDependencies": {
        "@nuxtjs/tailwindcss": "^6.14.0",
        "typescript": "^5.9.3",
        "@types/node": "^24.10.1"
    }
}
```

---

## 🗂️ Arborescence créée

```
frontend/
├── assets/
│   └── css/
│       └── main.css
├── composables/
│   └── useApi.ts
├── layouts/
│   └── default.vue
├── pages/
│   └── index.vue
├── stores/
│   ├── useUiStore.ts
│   └── useCounterStore.ts
├── .env.example
├── .gitignore
├── app.vue
├── nuxt.config.ts
├── package.json
├── QUICKSTART.md
├── README.md
├── tailwind.config.ts
└── tsconfig.json
```

---

## 🚀 Commandes pour tester

### Installation

```powershell
cd frontend
npm install
Copy-Item .env.example .env
```

### Démarrage

```powershell
npm run dev
```

### Tests de validation

1. Ouvrir http://localhost:3000
2. Vérifier que la page s'affiche
3. Tester les boutons du compteur Pinia
4. Vérifier que l'URL API est affichée
5. Vérifier que TailwindCSS fonctionne (styles colorés)

### Type checking

```powershell
npm run typecheck
```

---

## 🎨 Features principales

### 1. Composable useApi()

```typescript
const api = useApi();
const farms = await api.get("/api/farms");
const newFarm = await api.post("/api/farms", data);
```

### 2. Store Pinia (exemple UI)

```typescript
import { useUiStore } from "~/stores/useUiStore";
const uiStore = useUiStore();

uiStore.toggleSidebar();
uiStore.setTheme("dark");
uiStore.addNotification("success", "OK!");
```

### 3. Classes TailwindCSS custom

```html
<button class="btn-primary">Action</button>
<button class="btn-secondary">Annuler</button>
```

---

## 📝 Variables d'environnement

Fichier `.env` :

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000
NODE_ENV=development
```

---

## ✅ Checklist de validation

-   [x] Projet Nuxt 4 initialisé
-   [x] TypeScript configuré en mode strict
-   [x] Pinia installé et fonctionnel
-   [x] TailwindCSS avec thème custom
-   [x] Composable useApi() créé
-   [x] Layout par défaut opérationnel
-   [x] Page d'accueil de test affichée
-   [x] Variables d'env configurées
-   [x] Documentation complète créée
-   [x] Prêt pour génération client OpenAPI

---

## 🔜 Prochaines issues (EPIC-3)

1. **Issue #16** : Générer client OpenAPI depuis backend
2. **Issue #17** : Créer page Dashboard
3. **Issue #18** : Créer pages CRUD Farms/Reservoirs
4. **Issue #19** : Implémenter authentification JWT
5. **Issue #20** : Créer composants UI réutilisables

---

## 💾 Commit suggéré

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
- Complete documentation (README, QUICKSTART, implementation guide)

Tech Stack:
- Nuxt 4.2.1
- TypeScript 5.9.3 (strict)
- Pinia 3.0.4
- TailwindCSS 3.x

Closes #15
```

---

**Date de réalisation** : 20 novembre 2025  
**Temps estimé** : Configuration complète  
**Statut** : ✅ **PRÊT POUR COMMIT**
