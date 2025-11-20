<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo / Title -->
          <div class="flex items-center">
            <NuxtLink to="/" class="flex items-center space-x-2">
              <svg
                class="h-8 w-8 text-primary-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                />
              </svg>
              <span class="text-xl font-bold text-gray-900">HydroSense</span>
            </NuxtLink>
          </div>

          <!-- Navigation -->
          <nav class="hidden md:flex space-x-8">
            <NuxtLink
              to="/"
              class="text-gray-600 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors"
            >
              Accueil
            </NuxtLink>
            <NuxtLink
              to="/dashboard"
              class="text-gray-600 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors"
            >
              Dashboard
            </NuxtLink>
            <NuxtLink
              to="/farms"
              class="text-gray-600 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-colors"
            >
              Fermes
            </NuxtLink>
          </nav>

          <!-- Actions -->
          <div class="flex items-center space-x-4">
            <button
              @click="toggleTheme"
              class="p-2 text-gray-600 hover:text-primary-600 rounded-lg transition-colors"
              title="Changer le thème"
            >
              <svg
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <p class="text-center text-sm text-gray-500">
          © {{ currentYear }} HydroSense - Gestion hydroponique intelligente
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { useUiStore } from "~/stores/useUiStore";

const uiStore = useUiStore();
const currentYear = new Date().getFullYear();

const toggleTheme = () => {
  const newTheme = uiStore.theme === "light" ? "dark" : "light";
  uiStore.setTheme(newTheme);
};
</script>
