# Proxy Edge - Documentation

## 🎯 Vue d'ensemble

Le proxy **Edge** est une couche de sécurité côté serveur Nuxt qui intercepte tous les appels API du frontend et les forward vers le backend Symfony avec le JWT utilisateur.

## 🔒 Principe de sécurité

**Le navigateur ne doit JAMAIS appeler directement le backend Symfony.**

Tous les appels passent par le proxy edge qui :
1. Récupère la session Better Auth côté serveur
2. Extrait le JWT access token
3. Propage le token vers Symfony
4. Renvoie la réponse au frontend

## 🏗️ Architecture

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ fetch('/api/edge/reservoirs')
       ↓
┌─────────────────────────────────────┐
│  Nuxt Server (Edge Proxy)           │
│  ┌───────────────────────────────┐  │
│  │ 1. getSession()               │  │
│  │ 2. Extract JWT                │  │
│  │ 3. Add Authorization header   │  │
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │ HTTP + Bearer token
       ↓
┌─────────────────────┐
│  Symfony Backend    │
│  (API Platform)     │
└─────────────────────┘
       │ Response
       ↓
┌─────────────────────────────────────┐
│  Nuxt Server (Edge Proxy)           │
│  Forward response                   │
└──────┬──────────────────────────────┘
       │
       ↓
┌─────────────┐
│   Browser   │
└─────────────┘
```

## 📂 Structure

```
frontend/server/api/edge/
├── ping.get.ts         # Route de test
└── [...path].ts        # Proxy universel
```

## 🚀 Utilisation

### 1. Route de test

Vérifier que le proxy fonctionne :

```typescript
// Appel
const { data } = await useFetch('/api/edge/ping');

// Réponse
{ ok: true }
```

### 2. Appels API via le proxy

**❌ Avant (appel direct - NE PAS FAIRE) :**
```typescript
// MAUVAIS : appel direct au backend
const { data } = await useFetch('https://api.hydrosense.local/api/reservoirs');
```

**✅ Après (via proxy edge) :**
```typescript
// BON : appel via le proxy edge
const { data } = await useFetch('/api/edge/reservoirs');
```

### 3. Exemples complets

#### Lister des réservoirs (GET)

```vue
<script setup lang="ts">
const { data: reservoirs, error } = await useFetch('/api/edge/reservoirs', {
  method: 'GET',
});

if (error.value) {
  console.error('Erreur lors du chargement des réservoirs:', error.value);
}
</script>

<template>
  <div>
    <h1>Mes réservoirs</h1>
    <ul>
      <li v-for="reservoir in reservoirs" :key="reservoir.id">
        {{ reservoir.name }}
      </li>
    </ul>
  </div>
</template>
```

#### Créer une mesure (POST)

```vue
<script setup lang="ts">
const createMeasurement = async (data: any) => {
  const { data: measurement, error } = await useFetch('/api/edge/measurements', {
    method: 'POST',
    body: {
      reservoir: '/api/reservoirs/123',
      value: 42.5,
      unit: 'liters',
      measuredAt: new Date().toISOString(),
    },
  });

  if (error.value) {
    console.error('Erreur lors de la création:', error.value);
    return null;
  }

  return measurement.value;
};
</script>
```

#### Mettre à jour un profil (PATCH)

```vue
<script setup lang="ts">
const updateProfile = async (userId: string, updates: any) => {
  const { data, error } = await useFetch(`/api/edge/users/${userId}`, {
    method: 'PATCH',
    body: updates,
  });

  if (error.value) {
    console.error('Erreur lors de la mise à jour:', error.value);
    return null;
  }

  return data.value;
};
</script>
```

#### Supprimer une ressource (DELETE)

```vue
<script setup lang="ts">
const deleteReservoir = async (id: string) => {
  const { error } = await useFetch(`/api/edge/reservoirs/${id}`, {
    method: 'DELETE',
  });

  if (error.value) {
    console.error('Erreur lors de la suppression:', error.value);
    return false;
  }

  return true;
};
</script>
```

#### Avec query parameters

```typescript
// GET /api/reservoirs?farm=123&status=active
const { data } = await useFetch('/api/edge/reservoirs', {
  query: {
    farm: '123',
    status: 'active',
  },
});
```

### 4. Utilisation dans un composable

```typescript
// composables/useReservoirs.ts
export const useReservoirs = () => {
  const fetchReservoirs = async () => {
    const { data, error } = await useFetch('/api/edge/reservoirs');
    
    if (error.value) {
      throw createError({
        statusCode: error.value.statusCode,
        message: 'Impossible de charger les réservoirs',
      });
    }
    
    return data.value;
  };

  const createReservoir = async (reservoir: any) => {
    const { data, error } = await useFetch('/api/edge/reservoirs', {
      method: 'POST',
      body: reservoir,
    });
    
    if (error.value) {
      throw createError({
        statusCode: error.value.statusCode,
        message: 'Impossible de créer le réservoir',
      });
    }
    
    return data.value;
  };

  return {
    fetchReservoirs,
    createReservoir,
  };
};
```

Utilisation dans un composant :

```vue
<script setup lang="ts">
const { fetchReservoirs, createReservoir } = useReservoirs();

const reservoirs = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    reservoirs.value = await fetchReservoirs();
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
});
</script>
```

## ⚙️ Configuration

### Variables d'environnement

Dans `.env` :

```bash
# URL du backend Symfony
API_URL=http://localhost:8000

# Ou en production
API_URL=https://api.hydrosense.com
```

### nuxt.config.ts

```typescript
export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      apiBase: process.env.API_URL || 'http://localhost:8000',
    },
  },
});
```

## 🔐 Gestion de la session

Le proxy edge utilise Better Auth pour récupérer la session côté serveur :

```typescript
// Dans [...path].ts
const session = await auth.api.getSession({
  headers: event.node.req.headers as HeadersInit,
});

const accessToken = session.user.accessToken;
```

### Où est stocké le JWT ?

Le JWT est stocké dans la session Better Auth après l'authentification via Keycloak.

**Important :** Adaptez cette ligne dans `[...path].ts` selon votre configuration :

```typescript
const accessToken = (session.user as any).accessToken || 
                   (session.session as any).accessToken ||
                   (session as any).accessToken;
```

## 🚨 Gestion d'erreurs

Le proxy edge gère automatiquement les erreurs :

### Erreur 401 - Non authentifié

```typescript
// Le proxy renvoie automatiquement une erreur 401
// si l'utilisateur n'est pas connecté
throw createError({
  statusCode: 401,
  message: 'Vous devez être authentifié',
});
```

### Erreur du backend

```typescript
// Les erreurs du backend Symfony sont propagées
try {
  const { data } = await useFetch('/api/edge/reservoirs');
} catch (error) {
  // error.statusCode = code d'erreur Symfony
  // error.message = message d'erreur Symfony
  console.error(error);
}
```

### Gestion dans le composant

```vue
<script setup lang="ts">
const { data, error } = await useFetch('/api/edge/reservoirs');

// Afficher l'erreur à l'utilisateur
if (error.value) {
  const errorMessage = error.value.data?.message || 
                      error.value.message || 
                      'Une erreur est survenue';
  
  console.error('Erreur API:', errorMessage);
}
</script>
```

## 🧪 Testing

### Tester le proxy

```bash
# 1. Tester la route ping
curl http://localhost:3000/api/edge/ping

# Réponse attendue :
# { "ok": true }

# 2. Tester avec authentification (dans le browser)
# Ouvrir la console du navigateur :
fetch('/api/edge/reservoirs')
  .then(r => r.json())
  .then(console.log);
```

## 📝 Checklist de migration

- [ ] Tous les appels API passent par `/api/edge/*`
- [ ] Aucun appel direct à `api.hydrosense.*` depuis le browser
- [ ] Les composables utilisent `/api/edge/` comme base URL
- [ ] La variable `API_URL` est configurée
- [ ] Le JWT est correctement extrait de la session Better Auth
- [ ] Les erreurs sont gérées proprement
- [ ] La route `/api/edge/ping` répond `{ ok: true }`

## 🎓 Bonnes pratiques

### 1. Centraliser les appels dans des composables

```typescript
// ✅ BON
// composables/useApi.ts
export const useApi = () => {
  const fetchResource = (path: string) => {
    return useFetch(`/api/edge/${path}`);
  };
  
  return { fetchResource };
};
```

### 2. Ne jamais exposer de secrets côté client

```typescript
// ❌ MAUVAIS - Ne JAMAIS faire ça
const token = 'secret-token';
fetch('/api/edge/reservoirs', {
  headers: { Authorization: `Bearer ${token}` }
});

// ✅ BON - Le token est géré par le proxy edge
fetch('/api/edge/reservoirs');
```

### 3. Typage TypeScript

```typescript
interface Reservoir {
  id: string;
  name: string;
  capacity: number;
}

const { data } = await useFetch<Reservoir[]>('/api/edge/reservoirs');
```

## 🐛 Troubleshooting

### Erreur 401 - Unauthorized

**Cause :** La session Better Auth n'est pas valide ou expirée.

**Solution :**
1. Vérifier que l'utilisateur est connecté
2. Recharger la session : `await fetchSession()`
3. Reconnecter l'utilisateur si nécessaire

### Erreur 500 - API base URL not configured

**Cause :** La variable `API_URL` n'est pas définie.

**Solution :**
```bash
# .env
API_URL=http://localhost:8000
```

### Erreur "No access token"

**Cause :** Le JWT n'est pas trouvé dans la session Better Auth.

**Solution :**
1. Vérifier la configuration Keycloak
2. Adapter l'extraction du token dans `[...path].ts`
3. Vérifier que les scopes incluent le token

### CORS errors

**Cause :** Normalement, il ne devrait PAS y avoir d'erreurs CORS car tout passe par le proxy.

**Si vous voyez des erreurs CORS :**
- Vérifiez que vous appelez bien `/api/edge/*` et non directement le backend
- Le navigateur ne doit jamais appeler directement Symfony

## 📚 Ressources

- [Nuxt Server API](https://nuxt.com/docs/guide/directory-structure/server)
- [Better Auth Documentation](https://www.better-auth.com/)
- [H3 Event Handlers](https://h3.unjs.io/)
- [API Platform](https://api-platform.com/)
