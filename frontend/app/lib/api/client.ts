/**
 * HydroSense API Client
 * 
 * Typed API client generated from OpenAPI spec.
 * Uses openapi-fetch for lightweight, type-safe API calls.
 * 
 * Base URL is configured from NUXT_PUBLIC_API_BASE_URL environment variable.
 */

import createClient from 'openapi-fetch'
import type { paths } from './schema'
import { useRuntimeConfig } from '#app'

/**
 * Create API client instance with base URL from runtime config
 * 
 * @example
 * ```ts
 * const api = useApiClient()
 * const { data, error } = await api.GET('/api/reservoirs')
 * ```
 */
export function useApiClient() {
  const config = useRuntimeConfig()
  
  return createClient<paths>({
    baseUrl: config.public.apiBaseUrl as string,
    headers: {
      'Content-Type': 'application/ld+json',
      'Accept': 'application/ld+json',
    },
  })
}

/**
 * Create API client with custom configuration
 * Useful for authenticated requests or custom headers
 * 
 * @example
 * ```ts
 * const api = createApiClient({
 *   headers: {
 *     'Authorization': `Bearer ${token}`
 *   }
 * })
 * ```
 */
export function createApiClient(options: {
  baseUrl?: string
  headers?: Record<string, string>
} = {}) {
  const config = useRuntimeConfig()
  
  return createClient<paths>({
    baseUrl: options.baseUrl || config.public.apiBaseUrl as string,
    headers: {
      'Content-Type': 'application/ld+json',
      'Accept': 'application/ld+json',
      ...options.headers,
    },
  })
}
