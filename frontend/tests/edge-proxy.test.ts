/**
 * Tests du proxy Edge
 *
 * Ce fichier contient des tests manuels à exécuter dans la console
 * du navigateur pour vérifier que le proxy Edge fonctionne correctement.
 *
 * ⚠️ Ces tests nécessitent d'être authentifié (session Better Auth valide)
 */

// ==================================================
// TEST 1 : Route ping (pas d'auth requise pour test)
// ==================================================

export async function testPing() {
  console.log("🧪 TEST 1 : Route ping");

  try {
    const response = await fetch("/api/edge/ping");
    const data = await response.json();

    if (data.ok === true) {
      console.log("✅ PASS : Ping réussie", data);
      return true;
    } else {
      console.error("❌ FAIL : Réponse inattendue", data);
      return false;
    }
  } catch (error) {
    console.error("❌ FAIL : Erreur lors du ping", error);
    return false;
  }
}

// ==================================================
// TEST 2 : GET avec authentification
// ==================================================

export async function testGet(path = "reservoirs") {
  console.log(`🧪 TEST 2 : GET /api/edge/${path}`);

  try {
    const response = await fetch(`/api/edge/${path}`);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    console.log("✅ PASS : GET réussie", data);
    return data;
  } catch (error) {
    console.error("❌ FAIL : Erreur GET", error);
    throw error;
  }
}

// ==================================================
// TEST 3 : POST avec authentification
// ==================================================

export async function testPost(path = "reservoirs", body = {}) {
  console.log(`🧪 TEST 3 : POST /api/edge/${path}`);

  const testData = {
    name: "Test Reservoir",
    capacity: 1000,
    ...body,
  };

  try {
    const response = await fetch(`/api/edge/${path}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(testData),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    console.log("✅ PASS : POST réussie", data);
    return data;
  } catch (error) {
    console.error("❌ FAIL : Erreur POST", error);
    throw error;
  }
}

// ==================================================
// TEST 4 : PATCH avec authentification
// ==================================================

export async function testPatch(path: string, body = {}) {
  console.log(`🧪 TEST 4 : PATCH /api/edge/${path}`);

  const updates = {
    capacity: 2000,
    ...body,
  };

  try {
    const response = await fetch(`/api/edge/${path}`, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/merge-patch+json",
      },
      body: JSON.stringify(updates),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    console.log("✅ PASS : PATCH réussie", data);
    return data;
  } catch (error) {
    console.error("❌ FAIL : Erreur PATCH", error);
    throw error;
  }
}

// ==================================================
// TEST 5 : DELETE avec authentification
// ==================================================

export async function testDelete(path: string) {
  console.log(`🧪 TEST 5 : DELETE /api/edge/${path}`);

  try {
    const response = await fetch(`/api/edge/${path}`, {
      method: "DELETE",
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    console.log("✅ PASS : DELETE réussie");
    return true;
  } catch (error) {
    console.error("❌ FAIL : Erreur DELETE", error);
    throw error;
  }
}

// ==================================================
// TEST 6 : Query parameters
// ==================================================

export async function testQueryParams(path = "reservoirs", params = {}) {
  console.log(`🧪 TEST 6 : GET avec query params /api/edge/${path}`);

  const queryParams = new URLSearchParams({
    page: "1",
    itemsPerPage: "10",
    ...params,
  }).toString();

  const url = `/api/edge/${path}?${queryParams}`;

  try {
    const response = await fetch(url);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    console.log("✅ PASS : GET avec query params réussie", data);
    return data;
  } catch (error) {
    console.error("❌ FAIL : Erreur GET avec query params", error);
    throw error;
  }
}

// ==================================================
// TEST 7 : Vérifier qu'aucun JWT n'est exposé
// ==================================================

export function testNoTokenExposed() {
  console.log("🧪 TEST 7 : Vérifier qu'aucun JWT n'est exposé");

  const hasTokenInLocalStorage =
    localStorage.getItem("token") !== null ||
    localStorage.getItem("accessToken") !== null ||
    localStorage.getItem("jwt") !== null;

  const hasTokenInSessionStorage =
    sessionStorage.getItem("token") !== null ||
    sessionStorage.getItem("accessToken") !== null ||
    sessionStorage.getItem("jwt") !== null;

  if (hasTokenInLocalStorage || hasTokenInSessionStorage) {
    console.error("❌ FAIL : Token trouvé dans le storage !");
    console.error("localStorage:", localStorage);
    console.error("sessionStorage:", sessionStorage);
    return false;
  }

  console.log("✅ PASS : Aucun token dans le storage");
  return true;
}

// ==================================================
// TEST 8 : Vérifier les cookies
// ==================================================

export function testCookies() {
  console.log("🧪 TEST 8 : Vérifier les cookies");

  const cookies = document.cookie;
  const hasBetterAuthCookie = cookies.includes("better-auth");

  if (hasBetterAuthCookie) {
    console.log("✅ PASS : Cookie Better Auth présent");
    return true;
  } else {
    console.error("❌ FAIL : Cookie Better Auth manquant");
    console.error("Cookies:", cookies);
    return false;
  }
}

// ==================================================
// TEST 9 : Gestion d'erreur 401
// ==================================================

export async function testUnauthorized() {
  console.log("🧪 TEST 9 : Test erreur 401 (sans authentification)");

  // Note : Ce test nécessite de se déconnecter d'abord
  console.log("⚠️  Pour tester, déconnectez-vous d'abord");

  try {
    const response = await fetch("/api/edge/reservoirs");

    if (response.status === 401) {
      console.log("✅ PASS : 401 correctement renvoyée");
      return true;
    } else {
      console.error("❌ FAIL : Attendu 401, reçu", response.status);
      return false;
    }
  } catch (error) {
    console.error("❌ FAIL : Erreur inattendue", error);
    return false;
  }
}

// ==================================================
// TEST 10 : Suite complète
// ==================================================

export async function runAllTests() {
  console.log("🚀 Lancement de la suite de tests complète");
  console.log("=".repeat(50));

  const results = {
    ping: false,
    get: false,
    noTokenExposed: false,
    cookies: false,
  };

  // Test 1 : Ping
  results.ping = await testPing();
  console.log("");

  // Test 2 : GET
  try {
    await testGet();
    results.get = true;
  } catch (error) {
    results.get = false;
  }
  console.log("");

  // Test 7 : No token exposed
  results.noTokenExposed = testNoTokenExposed();
  console.log("");

  // Test 8 : Cookies
  results.cookies = testCookies();
  console.log("");

  // Résumé
  console.log("=".repeat(50));
  console.log("📊 RÉSUMÉ DES TESTS");
  console.log("=".repeat(50));

  const passed = Object.values(results).filter((r) => r === true).length;
  const total = Object.keys(results).length;

  Object.entries(results).forEach(([test, result]) => {
    const icon = result ? "✅" : "❌";
    console.log(`${icon} ${test}`);
  });

  console.log("");
  console.log(`Total : ${passed}/${total} tests passés`);

  if (passed === total) {
    console.log("🎉 TOUS LES TESTS SONT PASSÉS !");
    return true;
  } else {
    console.log("⚠️  Certains tests ont échoué");
    return false;
  }
}

// ==================================================
// TEST 11 : CRUD complet sur une ressource
// ==================================================

export async function testFullCrud(resourcePath = "reservoirs") {
  console.log(`🧪 TEST 11 : CRUD complet sur ${resourcePath}`);
  console.log("=".repeat(50));

  let createdId: string | null = null;

  try {
    // CREATE
    console.log("1️⃣ CREATE");
    const created = await testPost(resourcePath, {
      name: `Test ${Date.now()}`,
      capacity: 1000,
    });
    createdId = created.id || created["@id"]?.split("/").pop();

    if (!createdId) {
      throw new Error("ID non trouvé dans la réponse de création");
    }

    console.log("");

    // READ
    console.log("2️⃣ READ");
    await testGet(`${resourcePath}/${createdId}`);
    console.log("");

    // UPDATE
    console.log("3️⃣ UPDATE");
    await testPatch(`${resourcePath}/${createdId}`, {
      capacity: 2000,
    });
    console.log("");

    // DELETE
    console.log("4️⃣ DELETE");
    await testDelete(`${resourcePath}/${createdId}`);
    console.log("");

    console.log("✅ CRUD complet réussi !");
    return true;
  } catch (error) {
    console.error("❌ CRUD échoué", error);

    // Cleanup si erreur
    if (createdId) {
      try {
        await testDelete(`${resourcePath}/${createdId}`);
        console.log("🧹 Nettoyage effectué");
      } catch (cleanupError) {
        console.error("⚠️  Erreur lors du nettoyage", cleanupError);
      }
    }

    return false;
  }
}

// ==================================================
// INSTRUCTIONS D'UTILISATION
// ==================================================

/**
 * Comment utiliser ces tests :
 *
 * 1. Ouvrir la console du navigateur (F12)
 *
 * 2. S'assurer d'être authentifié (connecté via Keycloak)
 *
 * 3. Exécuter les tests individuels :
 *
 *    await testPing();
 *    await testGet('reservoirs');
 *    testNoTokenExposed();
 *    testCookies();
 *
 * 4. Ou exécuter la suite complète :
 *
 *    await runAllTests();
 *
 * 5. Ou tester un CRUD complet :
 *
 *    await testFullCrud('reservoirs');
 *
 * 6. Vérifier les logs dans la console
 */

// ==================================================
// Export pour utilisation dans d'autres fichiers
// ==================================================

export default {
  testPing,
  testGet,
  testPost,
  testPatch,
  testDelete,
  testQueryParams,
  testNoTokenExposed,
  testCookies,
  testUnauthorized,
  runAllTests,
  testFullCrud,
};

// Message d'aide
console.log(`
🧪 Tests du proxy Edge disponibles !

Commandes disponibles :
- await testPing()
- await testGet('reservoirs')
- await testPost('reservoirs', { name: 'Test' })
- await testPatch('reservoirs/123', { capacity: 2000 })
- await testDelete('reservoirs/123')
- await testQueryParams('reservoirs', { farm: '123' })
- testNoTokenExposed()
- testCookies()
- await runAllTests()
- await testFullCrud('reservoirs')

Exemple :
  await runAllTests()
`);
