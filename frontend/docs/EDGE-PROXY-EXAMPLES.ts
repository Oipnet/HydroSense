/**
 * Exemple d'utilisation du proxy Edge pour gérer les réservoirs
 * 
 * Ce fichier démontre comment utiliser le proxy edge dans un composant Vue
 */

// ========================================
// Exemple 1 : Appel simple avec useFetch
// ========================================

// Dans un composant Vue :
/*
<script setup lang="ts">
const { data: reservoirs, error, pending } = await useFetch('/api/edge/reservoirs');

if (error.value) {
  console.error('Erreur:', error.value);
}
</script>

<template>
  <div>
    <div v-if="pending">Chargement...</div>
    <div v-else-if="error">Erreur: {{ error.message }}</div>
    <ul v-else>
      <li v-for="reservoir in reservoirs" :key="reservoir.id">
        {{ reservoir.name }}
      </li>
    </ul>
  </div>
</template>
*/

// ========================================
// Exemple 2 : Utilisation du composable useEdgeApi
// ========================================

/*
<script setup lang="ts">
import { useEdgeApi } from '~/composables/useEdgeApi';

const edgeApi = useEdgeApi();
const reservoirs = ref([]);
const loading = ref(false);
const error = ref(null);

// Charger les réservoirs
const loadReservoirs = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    reservoirs.value = await edgeApi.get('reservoirs');
  } catch (e) {
    error.value = e;
    console.error('Erreur:', e);
  } finally {
    loading.value = false;
  }
};

// Créer un réservoir
const createReservoir = async (data) => {
  try {
    const newReservoir = await edgeApi.post('reservoirs', {
      name: data.name,
      capacity: data.capacity,
      farm: `/api/farms/${data.farmId}`,
    });
    
    reservoirs.value.push(newReservoir);
    return newReservoir;
  } catch (e) {
    console.error('Erreur lors de la création:', e);
    throw e;
  }
};

// Mettre à jour un réservoir
const updateReservoir = async (id, updates) => {
  try {
    const updated = await edgeApi.patch(`reservoirs/${id}`, updates);
    
    // Mettre à jour localement
    const index = reservoirs.value.findIndex(r => r.id === id);
    if (index !== -1) {
      reservoirs.value[index] = updated;
    }
    
    return updated;
  } catch (e) {
    console.error('Erreur lors de la mise à jour:', e);
    throw e;
  }
};

// Supprimer un réservoir
const deleteReservoir = async (id) => {
  try {
    await edgeApi.delete(`reservoirs/${id}`);
    
    // Supprimer localement
    reservoirs.value = reservoirs.value.filter(r => r.id !== id);
  } catch (e) {
    console.error('Erreur lors de la suppression:', e);
    throw e;
  }
};

onMounted(() => {
  loadReservoirs();
});
</script>

<template>
  <div>
    <h1>Gestion des réservoirs</h1>
    
    <div v-if="loading">Chargement...</div>
    <div v-else-if="error">Erreur: {{ error.message }}</div>
    
    <div v-else>
      <ul>
        <li v-for="reservoir in reservoirs" :key="reservoir.id">
          {{ reservoir.name }} - {{ reservoir.capacity }}L
          <button @click="deleteReservoir(reservoir.id)">Supprimer</button>
        </li>
      </ul>
    </div>
  </div>
</template>
*/

// ========================================
// Exemple 3 : Composable métier dédié
// ========================================

// composables/useReservoirs.ts
/*
export const useReservoirs = () => {
  const edgeApi = useEdgeApi();
  const reservoirs = useState<any[]>('reservoirs', () => []);
  const loading = ref(false);

  const fetchAll = async (farmId?: string) => {
    loading.value = true;
    
    try {
      const query = farmId ? { farm: farmId } : undefined;
      reservoirs.value = await edgeApi.get('reservoirs', query);
    } finally {
      loading.value = false;
    }
  };

  const create = async (data: any) => {
    const newReservoir = await edgeApi.post('reservoirs', data);
    reservoirs.value.push(newReservoir);
    return newReservoir;
  };

  const update = async (id: string, data: any) => {
    const updated = await edgeApi.patch(`reservoirs/${id}`, data);
    const index = reservoirs.value.findIndex(r => r.id === id);
    if (index !== -1) {
      reservoirs.value[index] = updated;
    }
    return updated;
  };

  const remove = async (id: string) => {
    await edgeApi.delete(`reservoirs/${id}`);
    reservoirs.value = reservoirs.value.filter(r => r.id !== id);
  };

  return {
    reservoirs: readonly(reservoirs),
    loading: readonly(loading),
    fetchAll,
    create,
    update,
    remove,
  };
};
*/

// Utilisation dans un composant :
/*
<script setup lang="ts">
const { reservoirs, loading, fetchAll, create, remove } = useReservoirs();

onMounted(() => {
  fetchAll();
});

const handleCreate = async () => {
  await create({
    name: 'Réservoir A',
    capacity: 5000,
    farm: '/api/farms/123',
  });
};
</script>

<template>
  <div>
    <button @click="handleCreate">Créer un réservoir</button>
    
    <div v-if="loading">Chargement...</div>
    <ul v-else>
      <li v-for="reservoir in reservoirs" :key="reservoir.id">
        {{ reservoir.name }}
        <button @click="remove(reservoir.id)">Supprimer</button>
      </li>
    </ul>
  </div>
</template>
*/

// ========================================
// Exemple 4 : Avec gestion d'erreur avancée
// ========================================

/*
<script setup lang="ts">
const toast = useToast(); // Assume un système de notifications

const { data, error } = await useFetch('/api/edge/reservoirs', {
  onResponseError({ response }) {
    // Gestion d'erreur personnalisée
    if (response.status === 401) {
      toast.error('Vous devez être connecté');
      navigateTo('/login');
    } else if (response.status === 403) {
      toast.error('Vous n\'avez pas les droits nécessaires');
    } else if (response.status >= 500) {
      toast.error('Erreur serveur, veuillez réessayer plus tard');
    } else {
      toast.error(response._data?.message || 'Une erreur est survenue');
    }
  },
  onResponse({ response }) {
    // Succès
    if (response.ok) {
      toast.success('Données chargées avec succès');
    }
  },
});
</script>
*/

// ========================================
// Exemple 5 : Import CSV via le proxy edge
// ========================================

/*
<script setup lang="ts">
const edgeApi = useEdgeApi();
const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);

const handleFileUpload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;

  uploading.value = true;

  try {
    // Lire le fichier
    const reader = new FileReader();
    reader.onload = async (e) => {
      const csv = e.target?.result as string;
      
      // Envoyer au backend via le proxy edge
      const result = await edgeApi.post('measurements/import', {
        csv: csv,
        reservoirId: '123',
      });
      
      console.log('Import réussi:', result);
    };
    
    reader.readAsText(file);
  } catch (e) {
    console.error('Erreur import:', e);
  } finally {
    uploading.value = false;
  }
};
</script>

<template>
  <div>
    <input 
      ref="fileInput"
      type="file" 
      accept=".csv"
      @change="handleFileUpload"
    />
    <div v-if="uploading">Upload en cours...</div>
  </div>
</template>
*/

// ========================================
// Exemple 6 : Pagination
// ========================================

/*
<script setup lang="ts">
const edgeApi = useEdgeApi();
const page = ref(1);
const itemsPerPage = 30;
const reservoirs = ref([]);
const total = ref(0);

const loadPage = async () => {
  const data = await edgeApi.get('reservoirs', {
    page: page.value,
    itemsPerPage: itemsPerPage,
  });
  
  reservoirs.value = data['hydra:member'];
  total.value = data['hydra:totalItems'];
};

const nextPage = () => {
  page.value++;
  loadPage();
};

const prevPage = () => {
  if (page.value > 1) {
    page.value--;
    loadPage();
  }
};

onMounted(() => {
  loadPage();
});
</script>

<template>
  <div>
    <ul>
      <li v-for="reservoir in reservoirs" :key="reservoir.id">
        {{ reservoir.name }}
      </li>
    </ul>
    
    <div>
      <button @click="prevPage" :disabled="page === 1">Précédent</button>
      <span>Page {{ page }}</span>
      <button @click="nextPage">Suivant</button>
    </div>
  </div>
</template>
*/

// ========================================
// Exemple 7 : Tests du proxy edge
// ========================================

/*
// Dans un test Vitest ou dans la console du navigateur
const edgeApi = useEdgeApi();

// Test ping
const pingResult = await edgeApi.ping();
console.log('Ping:', pingResult); // true

// Test GET
const reservoirs = await edgeApi.get('reservoirs');
console.log('Réservoirs:', reservoirs);

// Test POST
const newReservoir = await edgeApi.post('reservoirs', {
  name: 'Test Reservoir',
  capacity: 1000,
});
console.log('Créé:', newReservoir);

// Test PATCH
const updated = await edgeApi.patch(`reservoirs/${newReservoir.id}`, {
  capacity: 2000,
});
console.log('Mis à jour:', updated);

// Test DELETE
await edgeApi.delete(`reservoirs/${newReservoir.id}`);
console.log('Supprimé');
*/

export {};
