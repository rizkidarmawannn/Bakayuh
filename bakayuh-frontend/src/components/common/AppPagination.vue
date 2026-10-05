<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    currentPage: number
    pageSize: number
    totalItems: number
    pageSizeOptions?: number[]
  }>(),
  {
    pageSizeOptions: () => [5, 10, 25, 50],
  }
)

const emit = defineEmits<{
  (e: 'update:currentPage', page: number): void
  (e: 'update:pageSize', size: number): void
}>()

const totalPages = computed(() => Math.max(1, Math.ceil(props.totalItems / props.pageSize)))

const startIndex = computed(() => {
  if (props.totalItems === 0) return 0
  return (props.currentPage - 1) * props.pageSize + 1
})

const endIndex = computed(() => {
  return Math.min(props.currentPage * props.pageSize, props.totalItems)
})

const visiblePages = computed(() => {
  const pages: (number | string)[] = []
  const maxButtons = 5
  const total = totalPages.value
  const current = props.currentPage

  if (total <= maxButtons) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
    if (current < total - 2) pages.push('...')
    pages.push(total)
  }
  return pages
})

function onSelectPage(page: number | string) {
  if (typeof page === 'number' && page !== props.currentPage) {
    emit('update:currentPage', page)
  }
}

function onPageSizeChange(e: Event) {
  const select = e.target as HTMLSelectElement
  emit('update:pageSize', Number(select.value))
}
</script>

<template>
  <div class="px-4 py-3 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
    <!-- Left Info & Page Size Selector -->
    <div class="flex items-center gap-4 text-slate-500">
      <div>
        Menampilkan
        <span class="font-bold text-slate-800">{{ startIndex }}</span>
        -
        <span class="font-bold text-slate-800">{{ endIndex }}</span>
        dari
        <span class="font-bold text-slate-800">{{ totalItems }}</span>
        data
      </div>

      <div class="flex items-center gap-1.5">
        <label for="pageSize" class="text-slate-400">Baris:</label>
        <select
          id="pageSize"
          :value="pageSize"
          class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-2 py-1 font-semibold focus:outline-none focus:ring-1 focus:ring-kemenkum-navy"
          @change="onPageSizeChange"
        >
          <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">
            {{ opt }}
          </option>
        </select>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div v-if="totalPages > 1" class="flex items-center gap-1 self-center sm:self-auto">
      <!-- Previous -->
      <button
        type="button"
        class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1"
        :class="currentPage === 1
          ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-slate-50'
          : 'border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-kemenkum-navy bg-white'"
        :disabled="currentPage === 1"
        @click="emit('update:currentPage', currentPage - 1)"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="hidden sm:inline">Sebelumnya</span>
      </button>

      <!-- Numbered Pages -->
      <div class="flex items-center gap-1">
        <template v-for="(p, idx) in visiblePages" :key="idx">
          <span v-if="p === '...'" class="px-2 py-1 text-slate-400 font-bold">...</span>
          <button
            v-else
            type="button"
            class="w-7 h-7 rounded-lg text-xs font-bold transition-all flex items-center justify-center border"
            :class="p === currentPage
              ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-xs ring-1 ring-kemenkum-navy'
              : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
            @click="onSelectPage(p)"
          >
            {{ p }}
          </button>
        </template>
      </div>

      <!-- Next -->
      <button
        type="button"
        class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1"
        :class="currentPage === totalPages
          ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-slate-50'
          : 'border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-kemenkum-navy bg-white'"
        :disabled="currentPage === totalPages"
        @click="emit('update:currentPage', currentPage + 1)"
      >
        <span class="hidden sm:inline">Selanjutnya</span>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>
