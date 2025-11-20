/**
 * Composable for Reservoirs API
 *
 * Provides type-safe access to Reservoir endpoints.
 * Uses useAsyncData for automatic SSR support and caching.
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * const { data: reservoirs, pending, error, refresh } = await useReservoirs()
 * const { data: reservoir } = await useReservoir(1)
 * </script>
 * ```
 */

import { useApiClient } from "~/lib/api/client";

/**
 * Fetch all reservoirs
 *
 * @param options - Query parameters for pagination, filtering, etc.
 * @returns useAsyncData result with reservoirs collection
 */
export function useReservoirs(
  options: {
    page?: number;
    itemsPerPage?: number;
  } = {}
) {
  return useAsyncData("reservoirs", async () => {
    const api = useApiClient();
    const { data, error } = await api.GET("/api/reservoirs", {
      params: {
        query: {
          page: options.page,
          itemsPerPage: options.itemsPerPage,
        },
      },
    });

    if (error) {
      throw createError({
        statusCode: 500,
        message: "Failed to fetch reservoirs",
        data: error,
      });
    }

    return data;
  });
}

/**
 * Fetch a single reservoir by ID
 *
 * @param id - Reservoir ID
 * @returns useAsyncData result with reservoir details
 */
export function useReservoir(id: number | string) {
  return useAsyncData(`reservoir-${id}`, async () => {
    const api = useApiClient();
    const { data, error } = await api.GET("/api/reservoirs/{id}", {
      params: {
        path: { id: String(id) },
      },
    });

    if (error) {
      throw createError({
        statusCode: error.status || 500,
        message: `Failed to fetch reservoir ${id}`,
        data: error,
      });
    }

    return data;
  });
}

/**
 * Create a new reservoir
 *
 * @param reservoir - Reservoir data
 * @returns Created reservoir
 */
export async function createReservoir(reservoir: {
  name: string;
  capacity: number;
  farm?: string;
}) {
  const api = useApiClient();

  const { data, error } = await api.POST("/api/reservoirs", {
    body: reservoir as any,
  });

  if (error) {
    throw createError({
      statusCode: error.status || 500,
      message: "Failed to create reservoir",
      data: error,
    });
  }

  return data;
}

/**
 * Update an existing reservoir
 *
 * @param id - Reservoir ID
 * @param reservoir - Reservoir data to update
 * @returns Updated reservoir
 */
export async function updateReservoir(
  id: number | string,
  reservoir: Partial<{
    name: string;
    capacity: number;
    farm?: string;
  }>
) {
  const api = useApiClient();

  const { data, error } = await api.PUT("/api/reservoirs/{id}", {
    params: {
      path: { id: String(id) },
    },
    body: reservoir as any,
  });

  if (error) {
    throw createError({
      statusCode: error.status || 500,
      message: `Failed to update reservoir ${id}`,
      data: error,
    });
  }

  return data;
}

/**
 * Delete a reservoir
 *
 * @param id - Reservoir ID
 */
export async function deleteReservoir(id: number | string) {
  const api = useApiClient();

  const { error } = await api.DELETE("/api/reservoirs/{id}", {
    params: {
      path: { id: String(id) },
    },
  });

  if (error) {
    throw createError({
      statusCode: error.status || 500,
      message: `Failed to delete reservoir ${id}`,
      data: error,
    });
  }
}
