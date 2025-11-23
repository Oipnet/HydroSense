/**
 * Proxy universel Edge - Forward toutes les requêtes vers le backend Symfony
 *
 * Ce proxy :
 * 1. Récupère la session Better Auth côté serveur (jamais côté browser)
 * 2. Extrait le JWT access token de la session
 * 3. Propage le token vers Symfony via Authorization header
 * 4. Forward la requête complète (méthode, path, body, querystring)
 * 5. Renvoie la réponse au frontend
 *
 * @route ANY /api/edge/*
 *
 * Exemples:
 * - GET /api/edge/reservoirs → GET {apiBase}/api/reservoirs
 * - POST /api/edge/measurements → POST {apiBase}/api/measurements
 * - GET /api/edge/users/123 → GET {apiBase}/api/users/123
 */

import { auth } from "../../../app/lib/auth";
import type { H3Event } from "h3";

export default defineEventHandler(async (event: H3Event) => {
  try {
    // 1. Récupérer la configuration runtime
    const config = useRuntimeConfig();
    const apiBase = config.public.apiBase;

    if (!apiBase) {
      throw createError({
        statusCode: 500,
        statusMessage: "API base URL not configured",
        message: "La variable d'environnement API_URL n'est pas définie",
      });
    }

    // 2. Récupérer la session Better Auth côté serveur
    const session = await auth.api.getSession({
      headers: event.node.req.headers as HeadersInit,
    });

    // 3. Vérifier que l'utilisateur est authentifié
    if (!session?.session || !session?.user) {
      throw createError({
        statusCode: 401,
        statusMessage: "Unauthorized",
        message: "Vous devez être authentifié pour accéder à cette ressource",
      });
    }

    // 4. Extraire le path depuis la route dynamique
    // /api/edge/reservoirs → reservoirs
    // /api/edge/users/123 → users/123
    const path = getRouterParam(event, "path") || "";

    // 5. Construire l'URL complète vers Symfony
    const targetUrl = `${apiBase}/api/${path}`;

    // 6. Récupérer la query string
    const query = getQuery(event);
    const queryString = new URLSearchParams(
      query as Record<string, string>
    ).toString();
    const fullUrl = queryString ? `${targetUrl}?${queryString}` : targetUrl;

    // 7. Récupérer la méthode HTTP et le body (si présent)
    const method = event.node.req.method || "GET";
    let body = undefined;

    if (["POST", "PUT", "PATCH"].includes(method)) {
      body = await readBody(event);
    }

    // 8. Extraire le JWT access token depuis la session
    // Note: Better Auth stocke le token dans session.user ou dans un champ spécifique
    // Adaptez cette ligne selon votre configuration Keycloak/Better Auth
    const accessToken =
      (session.user as any).accessToken ||
      (session.session as any).accessToken ||
      (session as any).accessToken;

    if (!accessToken) {
      throw createError({
        statusCode: 401,
        statusMessage: "No access token",
        message: "Aucun token d'accès trouvé dans la session",
      });
    }

    // 9. Forwarder la requête vers Symfony avec le JWT
    const response = await $fetch(fullUrl, {
      method: method as any,
      headers: {
        Authorization: `Bearer ${accessToken}`,
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: body,
      // Ne pas lancer d'exception sur les erreurs HTTP (on les forward)
      ignoreResponseError: true,
    });

    // 10. Renvoyer la réponse au frontend
    return response;
  } catch (error: any) {
    // Gestion d'erreurs propre
    console.error("[Edge Proxy] Error:", error);

    // Si c'est déjà une erreur H3, on la relance
    if (error.statusCode) {
      throw error;
    }

    // Sinon, on crée une erreur générique
    throw createError({
      statusCode: error.response?.status || 500,
      statusMessage: error.response?.statusText || "Internal Server Error",
      message:
        error.response?.data?.message ||
        error.message ||
        "Une erreur est survenue lors de la communication avec le backend",
      data: error.response?.data,
    });
  }
});
