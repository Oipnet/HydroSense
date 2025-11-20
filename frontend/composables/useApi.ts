/**
 * Composable pour les appels API
 * Wrapper autour de $fetch qui utilise automatiquement la base URL configurée
 */
export const useApi = () => {
  const config = useRuntimeConfig();
  const baseURL = config.public.apiBaseUrl;

  /**
   * Effectue une requête GET
   */
  const get = <T>(endpoint: string, options?: Parameters<typeof $fetch>[1]) => {
    return $fetch<T>(endpoint, {
      ...options,
      method: "GET",
      baseURL,
    });
  };

  /**
   * Effectue une requête POST
   */
  const post = <T>(
    endpoint: string,
    body?: any,
    options?: Parameters<typeof $fetch>[1]
  ) => {
    return $fetch<T>(endpoint, {
      ...options,
      method: "POST",
      baseURL,
      body,
    });
  };

  /**
   * Effectue une requête PUT
   */
  const put = <T>(
    endpoint: string,
    body?: any,
    options?: Parameters<typeof $fetch>[1]
  ) => {
    return $fetch<T>(endpoint, {
      ...options,
      method: "PUT",
      baseURL,
      body,
    });
  };

  /**
   * Effectue une requête PATCH
   */
  const patch = <T>(
    endpoint: string,
    body?: any,
    options?: Parameters<typeof $fetch>[1]
  ) => {
    return $fetch<T>(endpoint, {
      ...options,
      method: "PATCH",
      baseURL,
      body,
    });
  };

  /**
   * Effectue une requête DELETE
   */
  const del = <T>(endpoint: string, options?: Parameters<typeof $fetch>[1]) => {
    return $fetch<T>(endpoint, {
      ...options,
      method: "DELETE",
      baseURL,
    });
  };

  return {
    get,
    post,
    put,
    patch,
    delete: del,
    baseURL,
  };
};
