<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
// Configuration globale de l'application
useHead({
  htmlAttrs: {
    lang: "fr",
  },
});
</script>
