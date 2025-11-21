<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Auth Test</h1>
        <p class="mt-2 text-sm text-gray-600">
          Test de l'intégration Better Auth + Keycloak
        </p>
      </div>

      <div class="bg-white shadow rounded-lg p-6 space-y-6">
        <!-- État de la session -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-3">
            État de la session
          </h2>
          <div class="bg-gray-50 rounded p-4">
            <div class="flex items-center mb-2">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="
                  isAuthenticated
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800'
                "
              >
                {{ isAuthenticated ? "Authentifié" : "Non authentifié" }}
              </span>
            </div>
            <pre class="mt-3 text-xs text-gray-600 overflow-auto">{{
              JSON.stringify(session, null, 2)
            }}</pre>
          </div>
        </div>

        <!-- Actions -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Actions</h2>
          <div class="space-y-3">
            <button
              v-if="!isAuthenticated"
              @click="signIn"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Se connecter via Keycloak
            </button>

            <button
              @click="fetchSession"
              class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Rafraîchir la session
            </button>

            <button
              v-if="isAuthenticated"
              @click="signOut"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Se déconnecter
            </button>
          </div>
        </div>

        <!-- Informations -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Informations</h2>
          <div class="text-sm text-gray-600 space-y-2">
            <p>
              <strong>Endpoint session:</strong>
              <code class="bg-gray-100 px-2 py-1 rounded text-xs">
                GET /api/auth/session
              </code>
            </p>
            <p>
              <strong>Endpoint connexion:</strong>
              <code class="bg-gray-100 px-2 py-1 rounded text-xs">
                GET /api/auth/signin/keycloak
              </code>
            </p>
            <p>
              <strong>Endpoint callback:</strong>
              <code class="bg-gray-100 px-2 py-1 rounded text-xs">
                GET /api/auth/callback/keycloak
              </code>
            </p>
            <p>
              <strong>Endpoint déconnexion:</strong>
              <code class="bg-gray-100 px-2 py-1 rounded text-xs">
                POST /api/auth/signout
              </code>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Import explicite du composable
import { useAuth } from "~/composables/useAuth";

const { session, isAuthenticated, signIn, signOut, fetchSession } = useAuth();

// Charger la session au montage du composant
onMounted(async () => {
  await fetchSession();
});
</script>
