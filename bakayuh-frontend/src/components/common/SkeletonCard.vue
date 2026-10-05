<script setup lang="ts">
defineProps<{
  lines?: number
}>()
</script>

<template>
  <div class="card p-5 animate-pulse">
    <div class="skeleton h-3 w-1/3 mb-4 rounded" />
    <div class="skeleton h-7 w-2/3 mb-2 rounded" />
    <div v-for="i in (lines ?? 1)" :key="i" class="skeleton h-2.5 w-1/2 mt-2 rounded" />
  </div>
</template>