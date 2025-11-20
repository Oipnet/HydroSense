<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 text-primary">
      API Client Demo - Culture Profiles
    </h1>

    <!-- Loading State -->
    <div v-if="pending" class="text-center py-8">
      <p class="text-gray-600">Loading culture profiles...</p>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
    >
      <strong>Error:</strong> {{ error.message }}
      <div v-if="error.statusCode === 401" class="mt-2">
        <p class="text-sm">
          🔒 Authentication required. The API requires a JWT token.
        </p>
        <p class="text-sm mt-1">
          To test: Either disable authentication in backend or implement JWT
          login in frontend.
        </p>
      </div>
    </div>

    <!-- Success State -->
    <div v-else-if="profiles">
      <div class="mb-6 flex justify-between items-center">
        <p class="text-gray-600">
          Found {{ (profiles as any)?.["hydra:totalItems"] || 0 }} culture
          profile(s)
        </p>
        <button @click="() => refresh()" class="btn-secondary">Refresh</button>
      </div>

      <!-- Culture Profiles List -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="profile in (profiles as any)?.['hydra:member'] || profiles || []"
          :key="profile['@id'] || profile.id"
          class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <h2 class="text-xl font-semibold mb-2 text-primary">
            {{ profile.name }}
          </h2>
          <div class="space-y-2 text-gray-600 text-sm">
            <p v-if="profile.description" class="text-sm italic mb-2">
              {{ profile.description }}
            </p>
            <div class="border-t pt-2">
              <p>
                <strong>pH:</strong> {{ profile.phMin }} - {{ profile.phMax }}
              </p>
              <p>
                <strong>EC:</strong> {{ profile.ecMin }} -
                {{ profile.ecMax }} mS/cm
              </p>
              <p>
                <strong>Water Temp:</strong> {{ profile.waterTempMin }}°C -
                {{ profile.waterTempMax }}°C
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!((profiles as any)?.['hydra:member'] || profiles)?.length"
        class="text-center py-12"
      >
        <p class="text-gray-600 mb-4">No culture profiles found</p>
        <p class="text-sm text-gray-500">
          Culture profiles define ideal growing conditions
        </p>
      </div>
    </div>

    <!-- API Info -->
    <div class="mt-12 bg-gray-100 p-6 rounded-lg">
      <h2 class="text-xl font-bold mb-4">API Configuration</h2>
      <div class="space-y-2 text-sm">
        <p>
          <strong>Base URL:</strong>
          <code class="bg-white px-2 py-1 rounded">{{ apiBaseUrl }}</code>
        </p>
        <p>
          <strong>Endpoint:</strong>
          <code class="bg-white px-2 py-1 rounded"
            >GET /api/culture_profiles</code
          >
        </p>
        <p>
          <strong>Authentication:</strong>
          <code class="bg-white px-2 py-1 rounded">None (public endpoint)</code>
        </p>
        <p class="text-gray-600 mt-4">
          This page demonstrates the OpenAPI-generated TypeScript client using a
          public endpoint. All API calls are fully typed and validated against
          the backend spec.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Demo page showing how to use the generated API client with a public endpoint
const { data: profiles, pending, error, refresh } = await useCultureProfiles();

const config = useRuntimeConfig();
const apiBaseUrl = config.public.apiBaseUrl;
</script>
