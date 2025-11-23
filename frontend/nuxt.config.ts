// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2025-07-15",

  devtools: { enabled: true },

  modules: ["@pinia/nuxt", "@nuxtjs/tailwindcss"],

  css: ["@/assets/css/main.css"],

  runtimeConfig: {
    // Private keys (server-side only)
    betterAuthSecret:
      process.env.BETTER_AUTH_SECRET ||
      "development-secret-change-in-production",
    betterAuthUrl: process.env.BETTER_AUTH_URL || "http://localhost:3000",
    keycloakDiscoveryUrl:
      process.env.KEYCLOAK_DISCOVERY_URL ||
      "http://localhost:8080/realms/hydrosense/.well-known/openid-configuration",
    keycloakClientId: process.env.KEYCLOAK_CLIENT_ID || "hydrosense-web-bff",

    // Public keys (exposed to client-side)
    public: {
      apiBaseUrl:
        process.env.NUXT_PUBLIC_API_BASE_URL || "http://localhost:8000",
      apiBase: process.env.API_URL || "http://localhost:8000",
    },
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },
});
