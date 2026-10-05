<script setup lang="ts">
import { ref } from 'vue'

const props = withDefaults(
  defineProps<{
    maxSizeMb?: number
    accept?: string
    disabled?: boolean
    uploading?: boolean
  }>(),
  {
    maxSizeMb: 10,
    accept: '.pdf,.jpg,.jpeg,.png',
    disabled: false,
    uploading: false,
  }
)

const emit = defineEmits<{
  (e: 'select', file: File): void
}>()

const isDragging = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const errorMessage = ref<string | null>(null)

function handleFiles(files: FileList | null) {
  errorMessage.value = null
  if (!files || files.length === 0) return

  const file = files[0]
  if (!file) return

  const maxBytes = props.maxSizeMb * 1024 * 1024

  if (file.size > maxBytes) {
    errorMessage.value = `Ukuran berkas (${(file.size / (1024 * 1024)).toFixed(1)}MB) melebihi batas maksimal ${props.maxSizeMb}MB.`
    return
  }

  emit('select', file)
  if (fileInput.value) fileInput.value.value = ''
}

function onDrop(e: DragEvent) {
  isDragging.value = false
  if (props.disabled || props.uploading) return
  handleFiles(e.dataTransfer?.files ?? null)
}

function onChange(e: Event) {
  const target = e.target as HTMLInputElement
  handleFiles(target.files)
}
</script>

<template>
  <div class="space-y-2">
    <div
      class="border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer"
      :class="{
        'border-kemenkum-navy bg-kemenkum-navy/5': isDragging,
        'border-slate-300 hover:border-kemenkum-navy hover:bg-slate-50/60': !isDragging && !disabled && !uploading,
        'opacity-50 cursor-not-allowed bg-slate-50': disabled || uploading,
      }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
      @click="!disabled && !uploading && fileInput?.click()"
    >
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        :accept="accept"
        :disabled="disabled || uploading"
        @change="onChange"
      />

      <!-- Icon -->
      <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-500">
        <svg v-if="!uploading" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <svg v-else class="w-6 h-6 animate-spin text-kemenkum-navy" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
        </svg>
      </div>

      <p class="text-xs font-semibold text-slate-700">
        <span class="text-kemenkum-navy hover:underline">Pilih berkas</span> atau seret dan lepas di sini
      </p>
      <p class="text-[11px] text-slate-400 mt-1">
        Format yang didukung: PDF, JPG, PNG (Maksimal {{ maxSizeMb }}MB)
      </p>
    </div>

    <!-- Error message -->
    <p v-if="errorMessage" class="text-xs text-red-600 font-medium">
      {{ errorMessage }}
    </p>
  </div>
</template>