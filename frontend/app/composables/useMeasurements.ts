/**
 * Composable for Measurements API
 *
 * Provides type-safe access to Measurement endpoints.
 * Uses useAsyncData for automatic SSR support and caching.
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * const { data: measurements } = await useMeasurements({ reservoir: '/api/reservoirs/1' })
 * const { data: measurement } = await useMeasurement(1)
 * </script>
 * ```
 */

import { useApiClient } from "~/lib/api/client";

/**
 * Fetch all measurements
 *
 * @param options - Query parameters for filtering by reservoir, date range, etc.
 * @returns useAsyncData result with measurements collection
 */
export function useMeasurements(
  options: {
    reservoir?: string;
    "measuredAt[after]"?: string;
    "measuredAt[before]"?: string;
    page?: number;
    itemsPerPage?: number;
  } = {}
) {
  return useAsyncData("measurements", async () => {
    const api = useApiClient();
    const { data, error } = await api.GET("/api/measurements", {
      params: {
        query: options as any,
      },
    });

    if (error) {
      throw createError({
        statusCode: 500,
        message: "Failed to fetch measurements",
        data: error,
      });
    }

    return data;
  });
}

/**
 * Fetch a single measurement by ID
 *
 * @param id - Measurement ID
 * @returns useAsyncData result with measurement details
 */
export function useMeasurement(id: number | string) {
  return useAsyncData(`measurement-${id}`, async () => {
    const api = useApiClient();
    const { data, error } = await api.GET("/api/measurements/{id}", {
      params: {
        path: { id: String(id) },
      },
    });

    if (error) {
      throw createError({
        statusCode: error.status || 500,
        message: `Failed to fetch measurement ${id}`,
        data: error,
      });
    }

    return data;
  });
}

/**
 * Create a new measurement
 *
 * @param measurement - Measurement data
 * @returns Created measurement
 */
export async function createMeasurement(measurement: {
  reservoir: string;
  waterLevel: number;
  measuredAt: string;
  comment?: string;
}) {
  const api = useApiClient();

  const { data, error } = await api.POST("/api/measurements", {
    body: measurement as any,
  });

  if (error) {
    throw createError({
      statusCode: error.status || 500,
      message: "Failed to create measurement",
      data: error,
    });
  }

  return data;
}

/**
 * Update an existing measurement
 *
 * @param id - Measurement ID
 * @param measurement - Measurement data to update
 * @returns Updated measurement
 */
export async function updateMeasurement(
  id: number | string,
  measurement: Partial<{
    waterLevel: number;
    measuredAt: string;
    comment?: string;
  }>
) {
  const api = useApiClient();

  const { data, error } = await api.PUT("/api/measurements/{id}", {
    params: {
      path: { id: String(id) },
    },
    body: measurement as any,
  });

  if (error) {
    throw createError({
      statusCode: error.status || 500,
      message: `Failed to update measurement ${id}`,
      data: error,
    });
  }

  return data;
}

/**
 * Delete a measurement
 *
 * @param id - Measurement ID
 */
export async function deleteMeasurement(id: number | string) {
  const api = useApiClient();

  const { error } = await api.DELETE("/api/measurements/{id}", {
    params: {
      path: { id: String(id) },
    },
  });

  if (error) {
    throw createError({
      statusCode: error.status || 500,
      message: `Failed to delete measurement ${id}`,
      data: error,
    });
  }
}
