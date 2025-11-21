<script setup lang="ts">
definePageMeta({
  middleware: "auth",
});

const { user, signOut } = useAuth();

const handleLogout = async () => {
  await signOut();
};
</script>

<template>
  <div class="p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Dashboard</h1>
      <button
        @click="handleLogout"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded"
      >
        Se déconnecter
      </button>
    </div>
    <div v-if="user" class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-2">Bienvenue {{ user.name }} !</h2>
      <p class="text-gray-600">Email: {{ user.email }}</p>
    </div>
  </div>
</template>
