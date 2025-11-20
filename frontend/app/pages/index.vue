<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-12">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">
        🌱 HydroSense Frontend
      </h1>
      <p class="text-xl text-gray-600 mb-2">
        Nuxt 4 + TypeScript + Pinia + TailwindCSS
      </p>
      <div
        class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium"
      >
        ✅ Configuration initiale réussie
      </div>
    </div>

    <!-- Configuration Info -->
    <div class="grid md:grid-cols-2 gap-6 mb-12">
      <!-- API Configuration -->
      <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <svg
            class="h-5 w-5 mr-2 text-primary-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
            />
          </svg>
          Configuration API
        </h2>
        <div class="space-y-2">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Base URL:</span>
            <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{
              apiBaseUrl
            }}</code>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Status:</span>
            <span class="text-sm text-green-600 font-medium">{{
              apiStatus
            }}</span>
          </div>
        </div>
      </div>

      <!-- Store Demo -->
      <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <svg
            class="h-5 w-5 mr-2 text-secondary-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
            />
          </svg>
          Pinia Store Demo
        </h2>
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Counter:</span>
            <span class="text-2xl font-bold text-primary-600">{{
              counter.count
            }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-600">Double:</span>
            <span class="text-lg font-semibold text-gray-700">{{
              counter.doubleCount
            }}</span>
          </div>
          <div class="flex gap-2 mt-4">
            <button @click="counter.increment()" class="btn-primary text-sm">
              + Increment
            </button>
            <button @click="counter.decrement()" class="btn-secondary text-sm">
              - Decrement
            </button>
            <button
              @click="counter.reset()"
              class="px-3 py-1 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm"
            >
              Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tech Stack -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        🚀 Stack Technique
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="tech in techStack"
          :key="tech.name"
          class="text-center p-4 bg-gray-50 rounded-lg"
        >
          <div class="text-2xl mb-2">{{ tech.icon }}</div>
          <div class="text-sm font-medium text-gray-900">{{ tech.name }}</div>
          <div class="text-xs text-gray-500">{{ tech.version }}</div>
        </div>
      </div>
    </div>

    <!-- Next Steps -->
    <div class="mt-12 bg-primary-50 border border-primary-200 rounded-lg p-6">
      <h2 class="text-lg font-semibold text-primary-900 mb-3">
        📋 Prochaines étapes
      </h2>
      <ul class="space-y-2 text-sm text-primary-800">
        <li class="flex items-start">
          <span class="mr-2">✓</span>
          <span>Projet Nuxt 4 initialisé avec TypeScript</span>
        </li>
        <li class="flex items-start">
          <span class="mr-2">✓</span>
          <span>Pinia configuré et opérationnel</span>
        </li>
        <li class="flex items-start">
          <span class="mr-2">✓</span>
          <span>TailwindCSS intégré avec thème personnalisé</span>
        </li>
        <li class="flex items-start">
          <span class="mr-2">✓</span>
          <span>Composable useApi() prêt pour les appels backend</span>
        </li>
        <li class="flex items-start">
          <span class="mr-2">→</span>
          <span>Générer le client OpenAPI depuis le backend</span>
        </li>
        <li class="flex items-start">
          <span class="mr-2">→</span>
          <span>Créer les pages et composants métier</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCounterStore } from "~/stores/useCounterStore";

// Page meta
definePageMeta({
  layout: "default",
  title: "Accueil - HydroSense",
});

// Stores
const counter = useCounterStore();
const { baseURL } = useApi();

// Reactive data
const apiBaseUrl = baseURL;
const apiStatus = ref("Configured");

// Tech stack info
const techStack = [
  { name: "Nuxt", version: "4.x", icon: "💚" },
  { name: "TypeScript", version: "5.x", icon: "🔷" },
  { name: "Pinia", version: "2.x", icon: "🍍" },
  { name: "Tailwind", version: "3.x", icon: "🎨" },
];

// Check API health on mount (exemple)
onMounted(async () => {
  try {
    // On pourrait faire un appel de health check ici
    // await useApi().get('/health')
    apiStatus.value = "Ready";
  } catch (error) {
    apiStatus.value = "Not available";
    console.error("API check failed:", error);
  }
});
</script>
