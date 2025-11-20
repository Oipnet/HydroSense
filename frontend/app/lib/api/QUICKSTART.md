# Quick Start - OpenAPI Client

Guide rapide pour utiliser le client API généré dans HydroSense.

## 🚀 Démarrage rapide

### 1. Générer le client

```bash
# Dans le dossier frontend/
npm run generate:api
```

Cela génère `lib/api/schema.d.ts` depuis `../backend/public/openapi.json`.

### 2. Utiliser dans un composable

```typescript
// composables/useReservoirs.ts
import { useApiClient } from '~/lib/api/client'

export function useReservoirs() {
  const api = useApiClient()
  
  return useAsyncData('reservoirs', async () => {
    const { data, error } = await api.GET('/api/reservoirs')
    if (error) throw createError({ ... })
    return data
  })
}
```

### 3. Utiliser dans une page

```vue
<script setup lang="ts">
const { data: reservoirs, pending, error } = await useReservoirs()
</script>

<template>
  <div>
    <div v-if="pending">Loading...</div>
    <div v-else-if="error">Error: {{ error.message }}</div>
    <div v-else>
      <div v-for="r in reservoirs['hydra:member']" :key="r.id">
        {{ r.name }} - {{ r.capacity }} m³
      </div>
    </div>
  </div>
</template>
```

## 📖 Exemples

### Fetch collection avec pagination

```typescript
const api = useApiClient()

const { data } = await api.GET('/api/reservoirs', {
  params: {
    query: {
      page: 1,
      itemsPerPage: 30
    }
  }
})

console.log(data['hydra:totalItems']) // Total count
console.log(data['hydra:member'])     // Array of reservoirs
```

### Fetch single item

```typescript
const { data: reservoir } = await api.GET('/api/reservoirs/{id}', {
  params: {
    path: { id: '1' }
  }
})

console.log(reservoir.name)
console.log(reservoir.capacity)
```

### Create (POST)

```typescript
const { data: newReservoir } = await api.POST('/api/reservoirs', {
  body: {
    name: 'Basin A',
    capacity: 1000,
    farm: '/api/farms/1' // IRI reference
  }
})
```

### Update (PATCH)

```typescript
await api.PATCH('/api/reservoirs/{id}', {
  params: {
    path: { id: '1' }
  },
  body: {
    capacity: 1500
  }
})
```

### Delete

```typescript
await api.DELETE('/api/reservoirs/{id}', {
  params: {
    path: { id: '1' }
  }
})
```

### Filtrer une collection

```typescript
const { data } = await api.GET('/api/measurements', {
  params: {
    query: {
      reservoir: '/api/reservoirs/1',
      'measuredAt[after]': '2025-01-01',
      'measuredAt[before]': '2025-12-31'
    }
  }
})
```

## 🎯 Composables recommandés

### Pattern CRUD complet

```typescript
// composables/useReservoirs.ts
export function useReservoirs(options = {}) {
  const api = useApiClient()
  return useAsyncData('reservoirs', async () => {
    const { data, error } = await api.GET('/api/reservoirs', {
      params: { query: options }
    })
    if (error) throw createError({ statusCode: 500, message: 'Failed to fetch' })
    return data
  })
}

export function useReservoir(id: number | string) {
  const api = useApiClient()
  return useAsyncData(`reservoir-${id}`, async () => {
    const { data, error } = await api.GET('/api/reservoirs/{id}', {
      params: { path: { id: String(id) } }
    })
    if (error) throw createError({ statusCode: 404 })
    return data
  })
}

export async function createReservoir(payload: ReservoirInput) {
  const api = useApiClient()
  const { data, error } = await api.POST('/api/reservoirs', { body: payload })
  if (error) throw createError({ statusCode: 400 })
  return data
}
```

## 🔄 Régénération après changement d'API

```bash
# 1. Exporter la nouvelle spec depuis le backend
cd backend
php bin/console api:openapi:export --output=public/openapi.json

# 2. Régénérer les types
cd ../frontend
npm run generate:api

# 3. Vérifier
npm run typecheck
```

## 🔐 Authentification JWT

```typescript
// plugins/api-auth.ts
export default defineNuxtPlugin(() => {
  const token = useCookie('auth_token')
  
  const authenticatedApi = createApiClient({
    headers: {
      'Authorization': `Bearer ${token.value}`
    }
  })
  
  return {
    provide: {
      authenticatedApi
    }
  }
})

// Utilisation
const { $authenticatedApi } = useNuxtApp()
const { data } = await $authenticatedApi.GET('/api/me')
```

## 🧪 Page de test

Visitez `/api-demo` pour voir le client en action :

```bash
npm run dev
# Ouvrir http://localhost:3000/api-demo
```

## 📝 Types disponibles

```typescript
import type { paths, components } from '~/lib/api/schema'

// Types d'entités
type Reservoir = components['schemas']['Reservoir.jsonld']
type Measurement = components['schemas']['Measurement.jsonld']
type Farm = components['schemas']['Farm.jsonld']

// Types d'endpoints
type ReservoirCollection = paths['/api/reservoirs']['get']['responses']['200']['content']['application/ld+json']
```

## 💡 Tips

### 1. Utiliser useAsyncData pour le caching

```typescript
// ✅ Bon : cache automatique
const { data } = await useAsyncData('key', () => api.GET(...))

// ❌ Éviter : pas de cache
const { data } = await api.GET(...)
```

### 2. Gestion des erreurs

```typescript
const { data, error } = await api.GET('/api/reservoirs')

if (error) {
  // error.status => Code HTTP
  // error.data => Corps de la réponse
  throw createError({
    statusCode: error.status || 500,
    message: 'Failed to fetch'
  })
}
```

### 3. Types personnalisés

```typescript
// Extraire le type d'un membre de collection
type Reservoir = paths['/api/reservoirs']['get']['responses']['200']['content']['application/ld+json']['hydra:member'][number]
```

## 🔗 Ressources

- [Documentation complète](../../../backend/docs/ISSUE-16-OPENAPI-CLIENT.md)
- [README lib/api](./README.md)
- [openapi-fetch docs](https://openapi-ts.pages.dev/openapi-fetch/)

---

**Dernière mise à jour** : 20 novembre 2025
