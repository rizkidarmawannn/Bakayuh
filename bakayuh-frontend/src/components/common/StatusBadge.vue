<script setup lang="ts">
import type { StatusColor } from '@/types'

const props = defineProps<{
  label: string
  color?: StatusColor
}>()

const classMap: Record<StatusColor, string> = {
  green: 'badge-green',
  yellow: 'badge-yellow',
  red: 'badge-red',
  blue: 'badge-blue',
  gray: 'badge-gray',
}
</script>

<template>
  <span :class="classMap[props.color ?? 'gray']">
    {{ label }}
  </span>
</template>