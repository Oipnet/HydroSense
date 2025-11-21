# Better Auth + Keycloak SSO - Intégration Nuxt 4

## ✅ Installation Complétée

L'intégration de Better Auth avec Keycloak est maintenant en place dans le frontend Nuxt 4.

### Fichiers Créés

1. **`server/utils/auth.ts`** - Configuration Better Auth
2. **`server/api/auth/[...all].ts`** - Handler API pour toutes les routes d'authentification
3. **`.env.example`** - Variables d'environnement mises à jour

### Configuration

#### Variables d'Environnement

Créez un fichier `.env` dans `/frontend` avec:

```env
# Better Auth Configuration
BETTER_AUTH_SECRET=your-secret-key-min-32-characters-long
BETTER_AUTH_URL=http://localhost:3000

# Keycloak Configuration
KEYCLOAK_CLIENT_ID=hydrosense-web-bff
KEYCLOAK_DISCOVERY_URL=http://localhost:8080/realms/hydrosense/.well-known/openid-configuration
```

**Important**: Le `BETTER_AUTH_SECRET` doit être une chaîne aléatoire d'au moins 32 caractères en production.

### API Endpoints Disponibles

Better Auth expose automatiquement les endpoints suivants:

#### 1. Vérifier la Session

```ts
GET / api / auth / session;
```

Retourne la session courante ou `null` si non authentifié.

**Exemple d'utilisation:**

```ts
const session = await $fetch("/api/auth/session");
if (session) {
  console.log("Utilisateur connecté:", session.user);
} else {
  console.log("Non authentifié");
}
```

#### 2. Se Déconnecter

```ts
POST / api / auth / sign - out;
```

Détruit la session et déconnecte l'utilisateur.

**Exemple:**

```ts
await $fetch("/api/auth/sign-out", { method: "POST" });
```

#### 3. Authentification SSO (à configurer)

Better Auth nécessite une configuration supplémentaire pour les providers OAuth/OIDC.
Les routes seront disponibles une fois le provider Keycloak configuré:

```ts
GET /api/auth/sign-in/keycloak  // Redirection vers Keycloak
GET /api/auth/callback/keycloak // Callback après authentification
```

### Prochaines Étapes

#### 1. Configurer le Provider Keycloak

Better Auth nécessite une configuration spécifique du provider OIDC. La configuration actuelle est minimaliste et doit être étendue avec:

```ts
// Dans server/utils/auth.ts
import { betterAuth } from "better-auth";
import { oidc } from "better-auth/plugins"; // Plugin OIDC

export const auth = betterAuth({
  // ... configuration existante

  plugins: [
    oidc({
      providers: [
        {
          id: "keycloak",
          name: "Keycloak",
          discoveryUrl: process.env.KEYCLOAK_DISCOVERY_URL,
          clientId: process.env.KEYCLOAK_CLIENT_ID,
          clientSecret: "", // Vide pour PKCE
          pkce: true,
          scopes: ["openid", "profile", "email"],
        },
      ],
    }),
  ],
});
```

**Note**: La configuration exacte dépend de la version de Better Auth. Consulter la documentation officielle: https://www.better-auth.com/docs

#### 2. Créer un Composable Auth

Créer `composables/useAuth.ts` pour faciliter l'utilisation:

```ts
export const useAuth = () => {
  const session = useState<any>("session", () => null);

  const fetchSession = async () => {
    try {
      const data = await $fetch("/api/auth/session");
      session.value = data;
      return data;
    } catch (error) {
      session.value = null;
      return null;
    }
  };

  const signOut = async () => {
    await $fetch("/api/auth/sign-out", { method: "POST" });
    session.value = null;
    navigateTo("/");
  };

  const signIn = () => {
    // Rediriger vers l'endpoint de connexion Keycloak
    navigateTo("/api/auth/sign-in/keycloak", { external: true });
  };

  return {
    session: readonly(session),
    fetchSession,
    signOut,
    signIn,
    isAuthenticated: computed(() => !!session.value),
  };
};
```

#### 3. Créer un Middleware d'Authentification

Créer `middleware/auth.ts`:

```ts
export default defineNuxtRouteMiddleware(async (to) => {
  const { fetchSession, isAuthenticated } = useAuth();

  // Vérifier la session
  if (!isAuthenticated.value) {
    await fetchSession();
  }

  // Rediriger si non authentifié
  if (!isAuthenticated.value) {
    return navigateTo("/login");
  }
});
```

#### 4. Configurer une Base de Données (Optionnel)

Pour persister les utilisateurs et sessions, configurer une base de données:

```ts
// Dans server/utils/auth.ts
export const auth = betterAuth({
  // ... configuration existante

  database: {
    provider: "postgres",
    url: process.env.DATABASE_URL,
  },
});
```

Better Auth créera automatiquement les tables nécessaires.

### Test de l'Installation

1. **Démarrer le serveur dev:**

   ```bash
   npm run dev
   ```

2. **Tester l'endpoint session:**

   ```bash
   curl http://localhost:3000/api/auth/session
   ```

   Devrait retourner `null` (pas encore authentifié).

3. **Vérifier les logs:**
   Aucune erreur TypeScript ne devrait apparaître.

### Troubleshooting

#### Erreur: "Cannot find module 'better-auth'"

Réinstaller le package:

```bash
npm install better-auth
```

#### Erreur: "process is not defined"

Vérifier que `@types/node` est installé et que `tsconfig.json` inclut `"types": ["node"]`.

#### Erreur de configuration OIDC

Better Auth v1.x peut nécessiter des plugins spécifiques pour OIDC. Consulter:

- Documentation: https://www.better-auth.com/docs
- GitHub: https://github.com/better-auth/better-auth

### Références

- [Better Auth Documentation](https://www.better-auth.com/docs)
- [Nuxt 4 Server](https://nuxt.com/docs/guide/directory-structure/server)
- [Keycloak OIDC](https://www.keycloak.org/docs/latest/securing_apps/#_oidc)
- [Issue KEYCLOAK-4](../../../docs/EPIC-KEYCLOAK.md)

---

## ✅ Status Final

**Installation:** ✅ Complète  
**Configuration OIDC:** ✅ Endpoints créés (signin, callback, session, signout)  
**Composable:** ✅ `useAuth()` disponible  
**Page de test:** ✅ `/auth-test`  
**Date:** 21 novembre 2025  
**Branche:** `44-keycloak-2-ajouter-keycloak-en-dev-docker-compose`

### Fichiers Créés

**Serveur (API):**

- `server/utils/auth.ts` - Configuration Better Auth
- `server/api/auth/session.get.ts` - GET /api/auth/session
- `server/api/auth/signin/keycloak.get.ts` - GET /api/auth/signin/keycloak
- `server/api/auth/callback/keycloak.get.ts` - GET /api/auth/callback/keycloak
- `server/api/auth/signout.post.ts` - POST /api/auth/signout
- `server/api/auth/[...all].ts` - Handler Better Auth (catch-all)

**Client:**

- `composables/useAuth.ts` - Composable d'authentification
- `app/pages/auth-test.vue` - Page de test de l'authentification

**Configuration:**

- `.env.example` - Variables d'environnement
- `tsconfig.json` - Support Node.js types
- `.gitignore` - Exclusion de la DB SQLite

### Test de l'Installation

1. **Accéder à la page de test:**

   ```
   http://localhost:3000/auth-test
   ```

2. **Tester l'endpoint session:**

   ```bash
   curl http://localhost:3000/api/auth/session
   # Devrait retourner: {"session":null,"user":null}
   ```

3. **Tester la redirection Keycloak:**
   - Cliquer sur "Se connecter via Keycloak" sur `/auth-test`
   - Devrait rediriger vers Keycloak (si configuré et démarré)

### Notes Importantes

- ✅ L'endpoint `/api/auth/session` fonctionne et retourne null (pas de session)
- ⚠️ Le flow OAuth complet nécessite Keycloak en cours d'exécution
- ⚠️ PKCE est simplifié (utilise `plain` au lieu de `S256`) - à améliorer pour la production
- ⚠️ La gestion de session est basique - à compléter avec cookies sécurisés
- 📝 Better Auth v1.3.34 a une API complexe - approche hybride utilisée (endpoints manuels + Better Auth)
