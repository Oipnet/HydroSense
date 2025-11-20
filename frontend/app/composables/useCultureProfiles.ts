/**
 * Composable for Culture Profiles API
 *
 * Provides type-safe access to CultureProfile endpoints (public, no auth required).
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * const { data: profiles } = await useCultureProfiles()
 * </script>
 * ```
 */

import { useApiClient } from "~/lib/api/client";

/**
 * Fetch all culture profiles (public endpoint)
 *
 * @returns useAsyncData result with culture profiles collection
 */
export function useCultureProfiles() {
  return useAsyncData("culture-profiles", async () => {
    const api = useApiClient();
    const { data, error } = await api.GET("/api/culture_profiles");

    if (error) {
      throw createError({
        statusCode: 500,
        message: "Failed to fetch culture profiles",
        data: error,
      });
    }

    return data;
  });
}
