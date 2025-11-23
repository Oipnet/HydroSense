/**
 * Route de test pour vérifier que le proxy edge fonctionne
 *
 * @route GET /api/edge/ping
 * @returns { ok: true }
 */
export default defineEventHandler(() => {
  return {
    ok: true,
  };
});
