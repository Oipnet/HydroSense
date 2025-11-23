# 🚀 Quick Start Guide - Proxy Edge

> Guide rapide pour démarrer avec le proxy Edge en 5 minutes

## 🎯 En résumé

**Avant :** Le browser appelait directement Symfony → ❌ JWT exposé
**Après :** Le browser appelle Nuxt Edge → ✅ JWT côté serveur

## ⚡ Setup en 3 étapes

### 1. Configurer l'environnement

```bash
# Créer ou éditer .env
echo "API_URL=http://localhost:8000" >> .env
```

### 2. Tester que ça fonctionne

```bash
# Démarrer l'app
npm run dev

# Dans un autre terminal, tester
curl http://localhost:3000/api/edge/ping

# Attendu : { "ok": true }
```

### 3. Utiliser dans le code

```vue
<script setup lang="ts">
// ❌ AVANT
// const { data } = await useFetch('https://api.hydrosense.com/api/reservoirs');

// ✅ MAINTENANT
const { data } = await useFetch('/api/edge/reservoirs');
</script>
```

## 🎨 Exemples rapides

### GET

```typescript
const { data } = await useFetch('/api/edge/reservoirs');
```

### POST

```typescript
const { data } = await useFetch('/api/edge/reservoirs', {
  method: 'POST',
  body: { name: 'Tank A', capacity: 5000 }
});
```

### PATCH

```typescript
const { data } = await useFetch('/api/edge/reservoirs/123', {
  method: 'PATCH',
  body: { capacity: 3000 }
});
```

### DELETE

```typescript
await useFetch('/api/edge/reservoirs/123', {
  method: 'DELETE'
});
```

## 🛠️ Avec le composable helper

```typescript
const edgeApi = useEdgeApi();

// Toutes les méthodes en un seul composable
await edgeApi.get('reservoirs');
await edgeApi.post('reservoirs', { name: 'Tank A' });
await edgeApi.patch('reservoirs/123', { capacity: 3000 });
await edgeApi.delete('reservoirs/123');
```

## 🔄 Pattern de migration

### AVANT (à supprimer)

```typescript
const config = useRuntimeConfig();
const token = localStorage.getItem('token'); // ⚠️ Dangereux

const { data } = await useFetch(`${config.public.apiBaseUrl}/api/reservoirs`, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

### APRÈS (nouveau code)

```typescript
// Plus simple, plus sûr
const { data } = await useFetch('/api/edge/reservoirs');
```

**Réduction de 70% du code !** 🎉

## 📚 Aller plus loin

- **Documentation complète :** `docs/EDGE-PROXY.md`
- **Schéma du flux :** `docs/EDGE-PROXY-FLOW.md`
- **Guide de migration :** `docs/EDGE-PROXY-MIGRATION.md`
- **Exemples détaillés :** `docs/EDGE-PROXY-EXAMPLES.ts`

## ✅ Checklist

- [ ] `.env` configuré avec `API_URL`
- [ ] `/api/edge/ping` répond `{ ok: true }`
- [ ] Remplacé les appels directs par `/api/edge/*`
- [ ] Supprimé les tokens de `localStorage`
- [ ] Testé GET / POST / PATCH / DELETE

## 🆘 Problèmes ?

**Erreur 401 ?**
→ Vérifiez que vous êtes connecté (session Better Auth valide)

**Erreur 500 API base URL not configured ?**
→ Ajoutez `API_URL=http://localhost:8000` dans `.env`

**CORS errors ?**
→ Assurez-vous d'utiliser `/api/edge/*` et non l'URL directe du backend

## 🎉 C'est tout !

Vous êtes prêt à utiliser le proxy Edge de manière sécurisée.

**Le JWT reste côté serveur, votre app est plus sécurisée !** 🔒
