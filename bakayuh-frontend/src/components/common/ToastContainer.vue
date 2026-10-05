<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, remove } = useToast()

const bgMap = {
  success: 'border-l-4 border-green-500 bg-white text-slate-800',
  error: 'border-l-4 border-red-500 bg-white text-slate-800',
  warning: 'border-l-4 border-yellow-500 bg-white text-slate-800',
  info: 'border-l-4 border-blue-500 bg-white text-slate-800',
}
</script>

<template>
  <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)]">
    <TransitionGroup
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="card p-3.5 shadow-lg flex items-start justify-between gap-3 cursor-pointer"
        :class="bgMap[toast.type]"
        @click="remove(toast.id)"
      >
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold leading-tight">{{ toast.title }}</p>
          <p v-if="toast.message" class="text-[11px] text-slate-500 mt-1 leading-snug">{{ toast.message }}</p>
        </div>
        <button type="button" class="text-slate-400 hover:text-slate-600 text-sm leading-none">&times;</button>
      </div>
    </TransitionGroup>
  </div>
</template>