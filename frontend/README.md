# HydroSense Frontend - Nuxt 4

Application frontend pour HydroSense, construite avec Nuxt 4, TypeScript, Pinia et TailwindCSS.

## 🚀 Stack Technique

- **Framework**: Nuxt 4
- **Language**: TypeScript (strict mode)
- **State Management**: Pinia
- **Styling**: TailwindCSS 3
- **API Client**: openapi-fetch (type-safe, auto-generated from OpenAPI spec)

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
├── app/                      # Nuxt 4 app directory
│   ├── composables/
│   │   ├── useApi.ts         # Composable API legacy (deprecated)
│   │   ├── useReservoirs.ts  # Composable Reservoirs (typed)
│   │   └── useMeasurements.ts # Composable Measurements (typed)
│   ├── layouts/
│   │   └── default.vue       # Layout par défaut
│   ├── lib/
│   │   └── api/
│   │       ├── schema.d.ts   # Types générés depuis OpenAPI (auto)
│   │       ├── client.ts     # Client API type-safe
│   │       ├── README.md     # Documentation API client
│   │       └── QUICKSTART.md # Guide rapide
│   ├── pages/
│   │   ├── index.vue         # Page d'accueil
│   │   └── api-demo.vue      # Démonstration API client
│   ├── stores/
│   │   ├── useUiStore.ts     # Store UI (sidebar, theme, notifs)
│   │   └── useCounterStore.ts # Store exemple (à supprimer)
│   └── app.vue               # Point d'entrée de l'app
├── assets/
│   └── css/
│       └── main.css          # Styles Tailwind + customs
├── public/                   # Assets statiques
├── .env.example              # Template des variables d'env
├── .gitignore
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

### Client API Type-Safe (OpenAPI)

Le projet utilise un client API généré automatiquement depuis la spec OpenAPI du backend.

#### Générer le client

```bash
# Générer les types TypeScript depuis la spec OpenAPI
npm run generate:api
```

Cela crée `lib/api/schema.d.ts` avec tous les types de l'API.

#### Utiliser les composables typés

```typescript
// Endpoint public (pas d'auth requise)
const { data: profiles, pending, error, refresh } = await useCultureProfiles();

// Endpoints protégés (nécessitent JWT - à implémenter)
const {
  data: reservoirs,
  pending,
  error,
  refresh,
} = await useReservoirs({
  page: 1,
  itemsPerPage: 30,
});

// Single item
const { data: reservoir } = await useReservoir(1);

// Create
const newReservoir = await createReservoir({
  name: "Basin A",
  capacity: 1000,
  farm: "/api/farms/1",
});

// Update
await updateReservoir(1, { capacity: 1500 });

// Delete
await deleteReservoir(1);
```

#### Utiliser le client directement

```typescript
import { useApiClient } from "~/lib/api/client";

const api = useApiClient();

// Tous les appels sont fully typed
const { data, error } = await api.GET("/api/reservoirs", {
  params: {
    query: {
      page: 1,
      itemsPerPage: 30,
    },
  },
});
```

**Avantages :**

- ✅ **Type safety complet** : Autocomplete et validation TypeScript
- ✅ **Sync avec backend** : Types générés depuis la spec OpenAPI
- ✅ **Léger** : ~5KB (gzip) avec openapi-fetch
- ✅ **SSR ready** : Compatible avec useAsyncData

**Documentation complète :**

- [lib/api/README.md](./lib/api/README.md) - Documentation détaillée
- [lib/api/QUICKSTART.md](./lib/api/QUICKSTART.md) - Guide rapide
- [backend/docs/ISSUE-16-OPENAPI-CLIENT.md](../backend/docs/ISSUE-16-OPENAPI-CLIENT.md) - Détails d'implémentation

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

# Générer le client API depuis OpenAPI
npm run generate:api
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

- [x] Générer le client OpenAPI depuis le backend Symfony ✅
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
