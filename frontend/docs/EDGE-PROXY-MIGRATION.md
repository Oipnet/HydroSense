# Guide de migration vers Edge Proxy

Ce guide vous aide à migrer vos appels API existants vers le nouveau système Edge Proxy.

## 🎯 Objectif

Remplacer tous les appels directs au backend par des appels via `/api/edge/*`.

## 📋 Checklist de migration

### Phase 1 : Préparation

- [ ] Lire la documentation : `docs/EDGE-PROXY.md`
- [ ] Comprendre le flux : `docs/EDGE-PROXY-FLOW.md`
- [ ] Configurer `API_URL` dans `.env`
- [ ] Tester la route ping : `curl http://localhost:3000/api/edge/ping`

### Phase 2 : Migration du code

- [ ] Identifier tous les appels API directs
- [ ] Migrer les appels `useFetch` / `$fetch`
- [ ] Migrer les composables métier
- [ ] Supprimer les références à l'ancienne URL
- [ ] Supprimer la gestion manuelle des tokens

### Phase 3 : Tests

- [ ] Tester chaque endpoint migré
- [ ] Vérifier que le JWT est bien propagé
- [ ] Tester la gestion d'erreurs (401, 403, 500)
- [ ] Vérifier qu'aucun appel direct ne reste

### Phase 4 : Nettoyage

- [ ] Supprimer les anciennes fonctions d'authentification
- [ ] Nettoyer les imports inutiles
- [ ] Mettre à jour la documentation
- [ ] Former l'équipe

## 🔄 Patterns de migration

### Pattern 1 : useFetch simple

**❌ Avant :**
```typescript
const { data } = await useFetch('https://api.hydrosense.local/api/reservoirs');
```

**✅ Après :**
```typescript
const { data } = await useFetch('/api/edge/reservoirs');
```

---

### Pattern 2 : useFetch avec headers manuels

**❌ Avant :**
```typescript
const token = localStorage.getItem('token'); // ⚠️ RISQUE DE SÉCURITÉ
const { data } = await useFetch('https://api.hydrosense.local/api/reservoirs', {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
});
```

**✅ Après :**
```typescript
// Le token est automatiquement ajouté par le proxy edge
const { data } = await useFetch('/api/edge/reservoirs');
```

---

### Pattern 3 : $fetch dans un composable

**❌ Avant :**
```typescript
export const useReservoirs = () => {
  const config = useRuntimeConfig();
  const token = useAuthToken(); // Fonction custom

  const fetchAll = async () => {
    return await $fetch(`${config.public.apiBaseUrl}/api/reservoirs`, {
      headers: {
        'Authorization': `Bearer ${token.value}`,
      },
    });
  };

  return { fetchAll };
};
```

**✅ Après :**
```typescript
export const useReservoirs = () => {
  const edgeApi = useEdgeApi();

  const fetchAll = async () => {
    return await edgeApi.get('reservoirs');
  };

  return { fetchAll };
};
```

---

### Pattern 4 : Appel POST avec body

**❌ Avant :**
```typescript
const token = useAuthToken();

const createReservoir = async (data: any) => {
  return await $fetch('https://api.hydrosense.local/api/reservoirs', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token.value}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  });
};
```

**✅ Après :**
```typescript
const edgeApi = useEdgeApi();

const createReservoir = async (data: any) => {
  return await edgeApi.post('reservoirs', data);
};
```

---

### Pattern 5 : PATCH / UPDATE

**❌ Avant :**
```typescript
const token = useAuthToken();

const updateReservoir = async (id: string, updates: any) => {
  return await $fetch(`https://api.hydrosense.local/api/reservoirs/${id}`, {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token.value}`,
      'Content-Type': 'application/merge-patch+json',
    },
    body: updates,
  });
};
```

**✅ Après :**
```typescript
const edgeApi = useEdgeApi();

const updateReservoir = async (id: string, updates: any) => {
  return await edgeApi.patch(`reservoirs/${id}`, updates);
};
```

---

### Pattern 6 : DELETE

**❌ Avant :**
```typescript
const token = useAuthToken();

const deleteReservoir = async (id: string) => {
  await $fetch(`https://api.hydrosense.local/api/reservoirs/${id}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token.value}`,
    },
  });
};
```

**✅ Après :**
```typescript
const edgeApi = useEdgeApi();

const deleteReservoir = async (id: string) => {
  await edgeApi.delete(`reservoirs/${id}`);
};
```

---

### Pattern 7 : Query parameters

**❌ Avant :**
```typescript
const token = useAuthToken();
const farmId = '123';

const { data } = await useFetch(
  `https://api.hydrosense.local/api/reservoirs?farm=${farmId}&status=active`,
  {
    headers: {
      'Authorization': `Bearer ${token.value}`,
    },
  }
);
```

**✅ Après :**
```typescript
const farmId = '123';

const { data } = await useFetch('/api/edge/reservoirs', {
  query: {
    farm: farmId,
    status: 'active',
  },
});
```

---

### Pattern 8 : Gestion d'erreurs

**❌ Avant :**
```typescript
try {
  const data = await $fetch('https://api.hydrosense.local/api/reservoirs', {
    headers: {
      'Authorization': `Bearer ${token.value}`,
    },
  });
} catch (error: any) {
  if (error.response?.status === 401) {
    // Token expiré
    await refreshToken();
    // Réessayer...
  }
}
```

**✅ Après :**
```typescript
// Le proxy gère automatiquement le refresh du token
try {
  const edgeApi = useEdgeApi();
  const data = await edgeApi.get('reservoirs');
} catch (error: any) {
  if (error.statusCode === 401) {
    // Session expirée - rediriger vers login
    navigateTo('/login');
  }
}
```

---

### Pattern 9 : Composable métier complet

**❌ Avant :**
```typescript
// composables/useReservoirs.ts
export const useReservoirs = () => {
  const config = useRuntimeConfig();
  const { token } = useAuth();
  const baseUrl = config.public.apiBaseUrl;

  const fetchAll = async () => {
    return await $fetch(`${baseUrl}/api/reservoirs`, {
      headers: { 'Authorization': `Bearer ${token.value}` },
    });
  };

  const create = async (data: any) => {
    return await $fetch(`${baseUrl}/api/reservoirs`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token.value}` },
      body: data,
    });
  };

  const update = async (id: string, data: any) => {
    return await $fetch(`${baseUrl}/api/reservoirs/${id}`, {
      method: 'PATCH',
      headers: { 'Authorization': `Bearer ${token.value}` },
      body: data,
    });
  };

  const remove = async (id: string) => {
    await $fetch(`${baseUrl}/api/reservoirs/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token.value}` },
    });
  };

  return { fetchAll, create, update, remove };
};
```

**✅ Après :**
```typescript
// composables/useReservoirs.ts
export const useReservoirs = () => {
  const edgeApi = useEdgeApi();

  const fetchAll = async () => {
    return await edgeApi.get('reservoirs');
  };

  const create = async (data: any) => {
    return await edgeApi.post('reservoirs', data);
  };

  const update = async (id: string, data: any) => {
    return await edgeApi.patch(`reservoirs/${id}`, data);
  };

  const remove = async (id: string) => {
    await edgeApi.delete(`reservoirs/${id}`);
  };

  return { fetchAll, create, update, remove };
};
```

**Réduction du code : ~60% !** 🎉

---

## 🔍 Trouver les appels à migrer

### Recherche dans le code

```bash
# Rechercher tous les appels directs à l'API
grep -r "api.hydrosense" frontend/
grep -r "apiBaseUrl" frontend/
grep -r "Authorization.*Bearer" frontend/

# Rechercher les usages de token manuels
grep -r "localStorage.getItem.*token" frontend/
grep -r "useAuthToken" frontend/
```

### Patterns à rechercher

1. **URLs hardcodées**
   - `https://api.hydrosense.local`
   - `http://localhost:8000/api`
   - `config.public.apiBaseUrl + '/api'`

2. **Headers d'authentification manuels**
   - `Authorization: Bearer ${token}`
   - `headers: { 'Authorization': ... }`

3. **Gestion de tokens**
   - `localStorage.getItem('token')`
   - `useAuthToken()`
   - `refreshToken()`

## 🧪 Tests de migration

### Test 1 : Vérifier qu'aucun appel direct ne reste

```typescript
// Dans la console du navigateur (DevTools)
// Ouvrir l'onglet Network, filtrer par "Fetch/XHR"
// Recharger l'app et vérifier :

// ❌ NE DOIT PAS APPARAÎTRE :
// https://api.hydrosense.local/api/reservoirs

// ✅ DOIT APPARAÎTRE :
// http://localhost:3000/api/edge/reservoirs
```

### Test 2 : Vérifier que le JWT n'est pas dans le browser

```typescript
// Console du navigateur
console.log(localStorage); // ❌ Ne doit PAS contenir de token
console.log(sessionStorage); // ❌ Ne doit PAS contenir de token

// Network tab → Headers → Request Headers
// ❌ NE DOIT PAS contenir : Authorization: Bearer ...
// ✅ DOIT contenir : Cookie: better-auth-session=...
```

### Test 3 : Tester chaque endpoint

```typescript
// Dans un fichier de test ou la console
const edgeApi = useEdgeApi();

// GET
const reservoirs = await edgeApi.get('reservoirs');
console.log('✅ GET:', reservoirs);

// POST
const newReservoir = await edgeApi.post('reservoirs', {
  name: 'Test',
  capacity: 1000,
});
console.log('✅ POST:', newReservoir);

// PATCH
const updated = await edgeApi.patch(`reservoirs/${newReservoir.id}`, {
  capacity: 2000,
});
console.log('✅ PATCH:', updated);

// DELETE
await edgeApi.delete(`reservoirs/${newReservoir.id}`);
console.log('✅ DELETE: success');
```

## 📝 Checklist par fichier

Pour chaque fichier contenant des appels API :

```markdown
- [ ] `app/composables/useReservoirs.ts`
  - [ ] Remplacer par `useEdgeApi`
  - [ ] Supprimer les headers d'auth
  - [ ] Tester les méthodes GET/POST/PATCH/DELETE

- [ ] `app/composables/useMeasurements.ts`
  - [ ] Même process

- [ ] `app/pages/dashboard.vue`
  - [ ] Vérifier les useFetch
  - [ ] Tester le chargement des données

- [ ] `app/components/ReservoirForm.vue`
  - [ ] Vérifier la création/mise à jour
  - [ ] Tester la validation

... (continuer pour chaque fichier)
```

## 🚨 Points d'attention

### 1. Configuration Keycloak / Better Auth

Le JWT doit être correctement stocké dans la session Better Auth.

**Vérifier dans `[...path].ts` :**
```typescript
const accessToken = (session.user as any).accessToken;
```

Si le token n'est pas trouvé, adaptez selon votre config :
```typescript
const accessToken = 
  (session.user as any).accessToken || 
  (session.session as any).accessToken ||
  (session as any).accessToken;
```

### 2. Gestion du refresh token

Better Auth gère automatiquement le refresh du token JWT.

**Pas besoin de :**
- Implémenter un mécanisme de refresh manuel
- Gérer l'expiration du token côté client
- Stocker le refresh token

### 3. CORS

Le proxy edge élimine les problèmes CORS car :
- Le browser appelle le même domaine (Nuxt)
- Nuxt fait l'appel serveur-à-serveur vers Symfony
- Pas de restriction same-origin

### 4. Rate limiting

Si vous avez du rate limiting :
- Le limiter côté Nuxt edge (IP du serveur Nuxt)
- OU côté Symfony (JWT user ID)
- Ne PAS limiter par IP browser (tous passeront par Nuxt)

## 📚 Ressources

- **Documentation complète :** `docs/EDGE-PROXY.md`
- **Exemples de code :** `docs/EDGE-PROXY-EXAMPLES.ts`
- **Schéma du flux :** `docs/EDGE-PROXY-FLOW.md`
- **Composable helper :** `app/composables/useEdgeApi.ts`

## 🆘 Support

En cas de problème :

1. Vérifier les logs Nuxt : `npm run dev` → console serveur
2. Vérifier les logs Symfony : `docker compose logs backend`
3. Tester la route ping : `curl http://localhost:3000/api/edge/ping`
4. Vérifier la session Better Auth : `await auth.api.getSession(...)`

## ✅ Validation finale

Une fois la migration terminée :

```bash
# 1. Aucun appel direct dans le code
grep -r "api.hydrosense" frontend/app/
# → Aucun résultat attendu

# 2. Aucun token dans localStorage
# → Vérifier manuellement dans DevTools

# 3. Tous les appels passent par edge
# → Network tab : tous les appels API vers /api/edge/*

# 4. Route ping fonctionne
curl http://localhost:3000/api/edge/ping
# → { "ok": true }
```

🎉 **Migration réussie !**
