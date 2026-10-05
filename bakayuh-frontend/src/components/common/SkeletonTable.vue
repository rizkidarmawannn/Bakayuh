<script setup lang="ts">
defineProps<{
  rows?: number
  cols?: number
}>()
</script>

<template>
  <div class="table-container animate-pulse p-4">
    <div class="space-y-3">
      <div class="skeleton h-8 w-full rounded" />
      <div v-for="r in (rows ?? 6)" :key="r" class="skeleton h-10 w-full rounded" />
    </div>
  </div>
</template>