# HydroSense API Client

Client TypeScript généré automatiquement depuis la spec OpenAPI du backend API Platform.

## 📚 Fichiers

### `schema.d.ts` (généré)

Types TypeScript générés automatiquement depuis `backend/public/openapi.json`.

**⚠️ NE PAS MODIFIER MANUELLEMENT** - Ce fichier est régénéré à chaque changement de l'API.

Contient :

- Interface pour chaque endpoint (`paths`)
- Types pour toutes les entités (`components.schemas`)
- Types pour les opérations (GET, POST, PATCH, DELETE)
- Types Hydra (collections, pagination)

### `client.ts`

Client API configuré avec la base URL Nuxt.

**Fonctions exportées :**

#### `useApiClient()`

Crée une instance du client API avec la configuration par défaut.

```typescript
const api = useApiClient();

// Appel typé
const { data, error } = await api.GET("/api/reservoirs");
//     ^? { 'hydra:member': Reservoir[], 'hydra:totalItems': number }
```

#### `createApiClient(options)`

Crée une instance avec configuration personnalisée (pour authentification).

```typescript
const api = createApiClient({
  headers: {
    Authorization: `Bearer ${token}`,
  },
});
```

## 🔄 Régénération

### Quand régénérer ?

Régénérez les types à chaque changement de l'API backend :

- Nouvelle entité
- Modification de propriétés
- Nouveau endpoint
- Changement de validation

### Commandes

```bash
# 1. Exporter la spec OpenAPI depuis le backend
cd backend
php bin/console api:openapi:export --output=public/openapi.json

# 2. Régénérer les types TypeScript
cd ../frontend
npm run generate:api

# 3. Vérifier les types
npm run typecheck
```

### Script tout-en-un (optionnel)

Ajoutez dans `package.json` :

```json
{
  "scripts": {
    "generate:api:full": "cd ../backend && php bin/console api:openapi:export --output=public/openapi.json && cd ../frontend && npm run generate:api"
  }
}
```

## 🎯 Utilisation

### Dans un composable

```typescript
// app/composables/useMyEntity.ts
import { useApiClient } from '~/lib/api/client'

export function useMyEntity(id: number) {
  const api = useApiClient()

  return useAsyncData(
    `entity-${id}`,
    async () => {
      const { data, error } = await api.GET('/api/my_entities/{id}', {
        params: { path: { id: String(id) } }
      })

      if (error) throw createError({ ... })
      return data
    }
  )
}
```

### Dans une page

```vue
<script setup lang="ts">
import { useApiClient } from "~/lib/api/client";

const api = useApiClient();

// Fetch data
const { data } = await api.GET("/api/reservoirs", {
  params: {
    query: {
      page: 1,
      itemsPerPage: 30,
    },
  },
});

// Create
const { data: newReservoir } = await api.POST("/api/reservoirs", {
  body: {
    name: "Basin A",
    capacity: 1000,
  },
});

// Update
await api.PATCH("/api/reservoirs/{id}", {
  params: { path: { id: "1" } },
  body: { capacity: 1500 },
});

// Delete
await api.DELETE("/api/reservoirs/{id}", {
  params: { path: { id: "1" } },
});
</script>
```

## 🔐 Authentification

Pour ajouter un token JWT :

```typescript
// Plugin ou composable
const token = useCookie("auth_token");

const api = createApiClient({
  headers: {
    Authorization: `Bearer ${token.value}`,
  },
});
```

## 📖 Type Safety

### Autocomplete complet

L'IDE suggère automatiquement :

- Tous les endpoints disponibles
- Les paramètres query/path/body
- Les types de retour

### Erreurs à la compilation

```typescript
// ❌ Erreur : endpoint inexistant
api.GET("/api/not_exists");

// ❌ Erreur : paramètre manquant
api.GET("/api/reservoirs/{id}");

// ✅ OK : tout est typé
api.GET("/api/reservoirs/{id}", {
  params: { path: { id: "1" } },
});
```

## 🎨 Structure des types

### Paths (endpoints)

```typescript
import type { paths } from "./schema";

// paths['/api/reservoirs']['get'] => Type du GET /api/reservoirs
// paths['/api/reservoirs']['post'] => Type du POST /api/reservoirs
// paths['/api/reservoirs/{id}']['patch'] => Type du PATCH /api/reservoirs/{id}
```

### Components (entités)

```typescript
import type { components } from "./schema";

type Reservoir = components["schemas"]["Reservoir.jsonld"];
type Measurement = components["schemas"]["Measurement.jsonld"];
type Farm = components["schemas"]["Farm.jsonld"];
```

### Collections Hydra

```typescript
type ReservoirCollection = {
  "hydra:member": Reservoir[];
  "hydra:totalItems": number;
  "hydra:view"?: {
    "@id": string;
    "hydra:first"?: string;
    "hydra:last"?: string;
    "hydra:previous"?: string;
    "hydra:next"?: string;
  };
};
```

## 🔗 Ressources

- [openapi-typescript](https://openapi-ts.pages.dev/) - Génération de types
- [openapi-fetch](https://openapi-ts.pages.dev/openapi-fetch/) - Client fetch
- [API Platform](https://api-platform.com/) - Backend framework
- [Nuxt Composables](https://nuxt.com/docs/guide/directory-structure/composables) - Pattern Nuxt

## 📝 Notes

### Pourquoi openapi-fetch ?

- ✅ Léger (~5KB gzip)
- ✅ Tree-shakeable
- ✅ Fetch API native (pas d'axios)
- ✅ Type-safe à 100%
- ✅ SSR compatible

### Alternative : openapi-typescript-codegen

Si vous préférez un générateur de fonctions plutôt que de types :

```bash
npm install --save-dev openapi-typescript-codegen
npx openapi-typescript-codegen --input ../backend/public/openapi.json --output ./lib/api/generated
```

Génère des fonctions comme `ReservoirService.getReservoirs()`.

**Inconvénients :**

- Plus lourd
- Moins flexible
- Nécessite plus de configuration

---

**Maintenu automatiquement** par `npm run generate:api`
