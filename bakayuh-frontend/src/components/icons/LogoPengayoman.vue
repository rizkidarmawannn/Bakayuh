<script setup lang="ts">
import logoPengayomanUrl from '@/assets/logo-pengayoman.png'

withDefaults(
  defineProps<{
    size?: number | string
    showText?: boolean
  }>(),
  {
    size: 40,
    showText: true,
  }
)
</script>

<template>
  <div
    class="inline-flex items-center justify-center select-none flex-shrink-0"
    :style="{
      width: typeof size === 'number' ? `${size}px` : size,
      height: typeof size === 'number' ? `${size}px` : size,
    }"
  >
    <img
      :src="logoPengayomanUrl"
      alt="Logo Pengayoman Kementerian Hukum"
      class="w-full h-full object-contain rounded-lg shadow-sm"
      loading="eager"
    />
  </div>
</template>
