import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface Toast {
  id: number
  type: ToastType
  title: string
  message?: string
  duration: number
}

const toasts = ref<Toast[]>([])
let nextId = 0

export function useToast() {
  function show(type: ToastType, title: string, message?: string, duration = 4000) {
    const id = ++nextId
    toasts.value.push({ id, type, title, message, duration })
    setTimeout(() => remove(id), duration)
  }

  function remove(id: number) {
    const idx = toasts.value.findIndex((t) => t.id === id)
    if (idx > -1) toasts.value.splice(idx, 1)
  }

  return {
    toasts,
    success: (title: string, msg?: string) => show('success', title, msg),
    error: (title: string, msg?: string) => show('error', title, msg),
    warning: (title: string, msg?: string) => show('warning', title, msg),
    info: (title: string, msg?: string) => show('info', title, msg),
    remove,
  }
}