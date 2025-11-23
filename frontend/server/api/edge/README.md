# Edge Proxy - Routes sécurisées

## 🎯 Objectif

Ce dossier contient les routes du proxy sécurisé **Edge** qui intercepte tous les appels API du frontend et les transmet au backend Symfony avec authentification JWT.

## 📁 Structure

```
server/api/edge/
├── ping.get.ts     # Route de test
└── [...path].ts    # Proxy universel (catch-all)
```

## 🔒 Sécurité

**Principe fondamental :** Le navigateur ne doit JAMAIS appeler directement le backend.

Tout passe par ce proxy qui :

1. ✅ Récupère la session Better Auth côté serveur
2. ✅ Extrait le JWT access token
3. ✅ Ajoute `Authorization: Bearer <jwt>` dans les headers
4. ✅ Forward vers Symfony
5. ✅ Renvoie la réponse au frontend

## 🚀 Utilisation

### Dans vos composants Vue

```vue
<script setup lang="ts">
// ❌ NE PAS FAIRE - Appel direct
// const { data } = await useFetch('https://api.hydrosense.com/api/reservoirs');

// ✅ FAIRE - Via le proxy edge
const { data } = await useFetch("/api/edge/reservoirs");
</script>
```

### Méthodes HTTP supportées

- `GET /api/edge/reservoirs` → Liste
- `POST /api/edge/reservoirs` → Création
- `PATCH /api/edge/reservoirs/123` → Mise à jour partielle
- `PUT /api/edge/reservoirs/123` → Remplacement complet
- `DELETE /api/edge/reservoirs/123` → Suppression

## 🧪 Test

```bash
# Route ping (sans authentification requise pour le test)
curl http://localhost:3000/api/edge/ping
# Réponse: { "ok": true }
```

## 📚 Documentation complète

- **Guide complet :** [`/docs/EDGE-PROXY.md`](../../docs/EDGE-PROXY.md)
- **Exemples de code :** [`/docs/EDGE-PROXY-EXAMPLES.ts`](../../docs/EDGE-PROXY-EXAMPLES.ts)
- **Composable helper :** [`/app/composables/useEdgeApi.ts`](../../app/composables/useEdgeApi.ts)

## ⚙️ Configuration

Variable d'environnement requise :

```bash
# .env
API_URL=http://localhost:8000
```

Dans `nuxt.config.ts` :

```typescript
runtimeConfig: {
  public: {
    apiBase: process.env.API_URL || 'http://localhost:8000',
  },
}
```

## 🐛 Troubleshooting

| Erreur                          | Cause               | Solution                    |
| ------------------------------- | ------------------- | --------------------------- |
| 401 Unauthorized                | Session expirée     | Reconnecter l'utilisateur   |
| 500 API base URL not configured | `API_URL` manquante | Définir dans `.env`         |
| No access token                 | JWT introuvable     | Vérifier config Better Auth |

## 🔧 Maintenance

### Adapter l'extraction du JWT

Le JWT peut être stocké à différents endroits selon votre configuration Better Auth / Keycloak.

Dans `[...path].ts`, ligne ~75, adaptez si nécessaire :

```typescript
const accessToken =
  (session.user as any).accessToken ||
  (session.session as any).accessToken ||
  (session as any).accessToken;
```

### Ajouter des routes spécifiques

Si vous avez besoin d'une logique spécifique pour certaines routes, créez un nouveau fichier :

```typescript
// server/api/edge/reservoirs/import.post.ts
export default defineEventHandler(async (event) => {
  // Logique spécifique pour l'import
});
```

Les routes spécifiques ont priorité sur le catch-all `[...path].ts`.

## ✅ Checklist de déploiement

- [ ] `API_URL` configurée en production
- [ ] Better Auth correctement configuré avec Keycloak
- [ ] Tous les appels frontend utilisent `/api/edge/*`
- [ ] Aucun appel direct au backend depuis le navigateur
- [ ] Route `/api/edge/ping` répond correctement
- [ ] Les logs du serveur ne montrent pas d'erreurs JWT

---

**Note :** Ce proxy est essentiel pour la sécurité. Ne jamais le contourner !
