/**
 * Composable pour les appels API via le proxy Edge
 *
 * Ce composable centralise tous les appels au backend Symfony
 * en passant automatiquement par le proxy sécurisé /api/edge/
 *
 * Usage:
 * ```typescript
 * const { fetchReservoirs, createReservoir } = useEdgeApi();
 *
 * const reservoirs = await fetchReservoirs();
 * const newReservoir = await createReservoir({ name: 'Tank A', capacity: 1000 });
 * ```
 */

export const useEdgeApi = () => {
  /**
   * Effectue un appel GET via le proxy edge
   */
  const get = async <T = any>(path: string, query?: Record<string, any>) => {
    try {
      return await $fetch<T>(`/api/edge/${path}`, {
        method: "GET",
        query,
      });
    } catch (error: any) {
      throw createError({
        statusCode: error.statusCode || 500,
        message: error.message || "Erreur lors de la récupération des données",
        data: error.data,
      });
    }
  };

  /**
   * Effectue un appel POST via le proxy edge
   */
  const post = async <T = any>(path: string, body: any) => {
    try {
      return await $fetch<T>(`/api/edge/${path}`, {
        method: "POST",
        body,
      });
    } catch (error: any) {
      throw createError({
        statusCode: error.statusCode || 500,
        message: error.message || "Erreur lors de la création",
        data: error.data,
      });
    }
  };

  /**
   * Effectue un appel PATCH via le proxy edge
   */
  const patch = async <T = any>(path: string, body: any) => {
    try {
      return await $fetch<T>(`/api/edge/${path}`, {
        method: "PATCH",
        body,
      });
    } catch (error: any) {
      throw createError({
        statusCode: error.statusCode || 500,
        message: error.message || "Erreur lors de la mise à jour",
        data: error.data,
      });
    }
  };

  /**
   * Effectue un appel PUT via le proxy edge
   */
  const put = async <T = any>(path: string, body: any) => {
    try {
      return await $fetch<T>(`/api/edge/${path}`, {
        method: "PUT",
        body,
      });
    } catch (error: any) {
      throw createError({
        statusCode: error.statusCode || 500,
        message: error.message || "Erreur lors du remplacement",
        data: error.data,
      });
    }
  };

  /**
   * Effectue un appel DELETE via le proxy edge
   */
  const del = async (path: string) => {
    try {
      await $fetch(`/api/edge/${path}`, {
        method: "DELETE" as any,
      });
      return true;
    } catch (error: any) {
      throw createError({
        statusCode: error.statusCode || 500,
        message: error.message || "Erreur lors de la suppression",
        data: error.data,
      });
    }
  };

  /**
   * Ping le proxy edge pour vérifier qu'il fonctionne
   */
  const ping = async () => {
    try {
      const data = await $fetch<{ ok: boolean }>("/api/edge/ping");
      return data?.ok || false;
    } catch (error: any) {
      throw createError({
        statusCode: error.statusCode || 500,
        message: "Le proxy edge ne répond pas",
      });
    }
  };

  return {
    get,
    post,
    patch,
    put,
    delete: del,
    ping,
  };
};
