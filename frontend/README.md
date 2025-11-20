# HydroSense Frontend - Nuxt 4

Application frontend pour HydroSense, construite avec Nuxt 4, TypeScript, Pinia et TailwindCSS.

## 🚀 Stack Technique

- **Framework**: Nuxt 4
- **Language**: TypeScript (strict mode)
- **State Management**: Pinia
- **Styling**: TailwindCSS 3
- **API Client**: Custom composable avec $fetch

## 📋 Prérequis

- Node.js >= 18.0.0
- npm >= 9.0.0

## 🛠️ Installation

### 1. Installer les dépendances

```bash
npm install
```

### 2. Configurer les variables d'environnement

Créer un fichier `.env` à la racine du projet frontend :

```bash
cp .env.example .env
```

Puis éditer `.env` :

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

### 3. Lancer le serveur de développement

```bash
npm run dev
```

L'application sera accessible sur `http://localhost:3000`

## 📂 Structure du Projet

```
frontend/
├── assets/
│   └── css/
│       └── main.css          # Styles Tailwind + customs
├── components/                # Composants Vue réutilisables
├── composables/
│   └── useApi.ts             # Composable pour appels API
├── layouts/
│   └── default.vue           # Layout par défaut
├── pages/
│   └── index.vue             # Page d'accueil
├── stores/
│   ├── useUiStore.ts         # Store UI (sidebar, theme, notifs)
│   └── useCounterStore.ts    # Store exemple (à supprimer)
├── .env.example              # Template des variables d'env
├── .gitignore
├── app.vue                   # Point d'entrée de l'app
├── nuxt.config.ts            # Configuration Nuxt
├── package.json
├── tailwind.config.ts        # Configuration Tailwind
└── tsconfig.json             # Configuration TypeScript
```

## 🎨 TailwindCSS

TailwindCSS est configuré avec un thème personnalisé incluant des couleurs pour HydroSense :

- **Primary**: Bleu (shades 50-950)
- **Secondary**: Vert (shades 50-950)

Classes utilitaires personnalisées disponibles :

- `.btn-primary` : Bouton primaire
- `.btn-secondary` : Bouton secondaire

## 🔌 API Integration

### Utiliser le composable `useApi()`

```typescript
// Dans un composant ou une page
const api = useApi();

// GET request
const farms = await api.get("/api/farms");

// POST request
const newFarm = await api.post("/api/farms", {
  name: "Ma Ferme",
  location: "Lyon",
});

// PUT/PATCH/DELETE
await api.put("/api/farms/1", data);
await api.patch("/api/farms/1", partialData);
await api.delete("/api/farms/1");
```

Le composable utilise automatiquement `NUXT_PUBLIC_API_BASE_URL` configurée dans `.env`.

## 📦 Pinia Stores

### Store UI (useUiStore)

Gère l'état global de l'interface :

```typescript
import { useUiStore } from "~/stores/useUiStore";

const uiStore = useUiStore();

// Sidebar
uiStore.toggleSidebar();

// Thème
uiStore.setTheme("dark");

// Notifications
uiStore.addNotification("success", "Opération réussie");
```

### Store Counter (exemple)

Store de démonstration - peut être supprimé une fois le projet avancé.

## 🧪 Scripts Disponibles

```bash
# Développement
npm run dev

# Build production
npm run build

# Preview production build
npm run preview

# Type checking
npm run typecheck

# Generate static site
npm run generate
```

## ✅ Tests de Validation

1. **Vérifier que l'app démarre** :

   ```bash
   npm run dev
   ```

   → Ouvrir `http://localhost:3000`

2. **Vérifier Tailwind** :
   → La page d'accueil doit afficher des styles colorés avec les classes Tailwind

3. **Vérifier Pinia** :
   → Cliquer sur les boutons Increment/Decrement, le compteur doit se mettre à jour

4. **Vérifier la config API** :
   → L'URL de l'API doit s'afficher sur la page d'accueil

5. **Vérifier TypeScript** :
   ```bash
   npm run typecheck
   ```
   → Aucune erreur ne doit apparaître

## 🔜 Prochaines Étapes

- [ ] Générer le client OpenAPI depuis le backend Symfony
- [ ] Créer les pages métier (Dashboard, Farms, Reservoirs, etc.)
- [ ] Implémenter l'authentification JWT
- [ ] Ajouter les composants de formulaires
- [ ] Mettre en place les tests (Vitest)

## 📚 Documentation

- [Nuxt 4 Documentation](https://nuxt.com)
- [Pinia Documentation](https://pinia.vuejs.org)
- [TailwindCSS Documentation](https://tailwindcss.com)

## 📝 Notes

- Ce projet utilise **Nuxt 4** (dernière version)
- TypeScript est configuré en mode strict
- Les composables Nuxt sont auto-importés
- Pinia est intégré via `@pinia/nuxt`
