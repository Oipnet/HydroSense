/**
 * Composable d'authentification
 *
 * Fournit des méthodes pour gérer l'authentification utilisateur
 * avec Keycloak SSO via Better Auth.
 *
 * Usage:
 * ```vue
 * <script setup>
 * const { session, isAuthenticated, signIn, signOut, fetchSession } = useAuth();
 *
 * // Charger la session au montage
 * onMounted(async () => {
 *   await fetchSession();
 * });
 * </script>
 * ```
 */

export interface AuthSession {
  session: {
    id: string;
    userId: string;
    expiresAt: Date;
  } | null;
  user: {
    id: string;
    email: string;
    name: string;
    roles?: string[];
  } | null;
}

export const useAuth = () => {
  // État de la session (partagé globalement)
  const session = useState<AuthSession>("auth:session", () => ({
    session: null,
    user: null,
  }));

  /**
   * Récupère la session courante depuis l'API
   */
  const fetchSession = async () => {
    try {
      const { authClient } = await import("~/lib/auth-client");
      const result = await authClient.getSession();

      if (result.data) {
        session.value = {
          session: result.data.session,
          user: result.data.user,
        };
        return session.value;
      } else {
        session.value = { session: null, user: null };
        return null;
      }
    } catch (error) {
      console.error("[Auth] Failed to fetch session:", error);
      session.value = { session: null, user: null };
      return null;
    }
  };

  /**
   * Initie la connexion SSO via Keycloak
   * Redirige vers Keycloak pour l'authentification
   */
  const signIn = async () => {
    if (!import.meta.client) {
      return;
    }

    try {
      const { authClient } = await import("~/lib/auth-client");

      const result = await authClient.signIn.oauth2({
        providerId: "keycloak",
        callbackURL: window.location.origin + "/dashboard",
      });

      if (result.data?.url) {
        window.location.href = result.data.url;
      }
    } catch (error) {
      console.error("[Auth] Error during sign in:", error);
      throw error;
    }
  };

  /**
   * Déconnecte l'utilisateur
   * Détruit la session locale et redirige vers la page de login
   */
  const signOut = async () => {
    if (!import.meta.client) {
      return;
    }

    try {
      const { authClient } = await import("~/lib/auth-client");
      await authClient.signOut();
      session.value = { session: null, user: null };
      
      // Redirection vers login
      navigateTo("/login");
    } catch (error) {
      console.error("[Auth] Failed to sign out:", error);
      navigateTo("/login");
    }
  };

  /**
   * Indique si l'utilisateur est authentifié
   */
  const isAuthenticated = computed(() => !!session.value.user);

  /**
   * Utilisateur courant (null si non authentifié)
   */
  const user = computed(() => session.value.user);

  return {
    session: readonly(session),
    user,
    isAuthenticated,
    fetchSession,
    signIn,
    signOut,
  };
};
