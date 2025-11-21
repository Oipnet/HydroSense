/**
 * Middleware d'authentification
 * Protège les routes nécessitant une authentification
 */
export default defineNuxtRouteMiddleware(async () => {
  const { fetchSession, isAuthenticated } = useAuth();

  // Vérifier la session si pas encore chargée
  if (!isAuthenticated.value) {
    await fetchSession();
  }

  // Si toujours pas authentifié, rediriger vers la page de connexion
  if (!isAuthenticated.value) {
    return navigateTo("/login");
  }
});
