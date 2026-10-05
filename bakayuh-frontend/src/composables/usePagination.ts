import { ref, computed, watch, type Ref } from 'vue'

export interface UsePaginationOptions {
  defaultPageSize?: number
}

export function usePagination<T>(
  items: Ref<T[]>,
  options: UsePaginationOptions = {}
) {
  const pageSize = ref(options.defaultPageSize ?? 10)
  const currentPage = ref(1)

  const totalItems = computed(() => items.value.length)
  const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / pageSize.value)))

  // Reset to page 1 if items count changes (e.g. search / filter applied)
  watch(
    () => items.value.length,
    () => {
      currentPage.value = 1
    }
  )

  watch(
    () => pageSize.value,
    () => {
      currentPage.value = 1
    }
  )

  const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return items.value.slice(start, start + pageSize.value)
  })

  function setPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  function nextPage() {
    if (currentPage.value < totalPages.value) {
      currentPage.value++
    }
  }

  function prevPage() {
    if (currentPage.value > 1) {
      currentPage.value--
    }
  }

  return {
    currentPage,
    pageSize,
    totalItems,
    totalPages,
    paginatedItems,
    setPage,
    nextPage,
    prevPage,
  }
}
