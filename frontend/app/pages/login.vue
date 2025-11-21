<script setup lang="ts">
const { isAuthenticated, signIn, fetchSession } = useAuth();

// Rediriger si déjà authentifié
onMounted(async () => {
  // Charger la session pour vérifier l'authentification
  await fetchSession();
  
  if (isAuthenticated.value) {
    await navigateTo("/dashboard");
  }
});

const handleLogin = async () => {
  try {
    await signIn();
  } catch (error) {
    console.error("[Login] Erreur lors de la connexion:", error);
  }
};
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100"
  >
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
      <!-- Logo / Header -->
      <div class="text-center">
        <h2 class="text-4xl font-extrabold text-gray-900">HydroSense</h2>
        <p class="mt-2 text-sm text-gray-600">
          Connectez-vous pour accéder à votre compte
        </p>
      </div>

      <!-- Login Card -->
      <div class="mt-8 space-y-6">
        <div class="rounded-md shadow-sm space-y-4">
          <!-- SSO Button -->
          <button
            @click="handleLogin"
            type="button"
            class="group relative w-full flex justify-center items-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
          >
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
              />
            </svg>
            Se connecter avec Keycloak
          </button>
        </div>

        <!-- Info Section -->
        <div class="text-center text-sm text-gray-500">
          <p>Authentification sécurisée via Keycloak SSO</p>
        </div>
      </div>
    </div>
  </div>
</template>
