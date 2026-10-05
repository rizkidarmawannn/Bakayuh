<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { RbTargetPeriode, StatusColor, PeriodeRb } from '@/types'
import StatusBadge from '@/components/common/StatusBadge.vue'
import FileUploadZone from '@/components/common/FileUploadZone.vue'

const route = useRoute()
const router = useRouter()
const { isSuperAdmin, isAdminKanwil, isOperatorSatker, user } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const subId = Number(route.params.id)
const satkerId = Number(route.query.satker_id)
const tahunId = Number(route.query.tahun_id)
const periode = (route.query.periode as PeriodeRb) || 'B03'

const loading = ref(true)
const target = ref<RbTargetPeriode | null>(null)

// Penjelasan ZI form
const penjelasanZi = ref('')
const isSavingPenjelasan = ref(false)

// Upload state
const isUploading = ref(false)
const isSubmitting = ref(false)

// Clarification chat state
const chatMessage = ref('')
const isSendingChat = ref(false)

// Verifikator Kanwil state
const isVerifying = ref(false)
const verifForm = reactive<{
  status_verifikasi: 'lengkap' | 'perlu_perbaikan' | 'tercapai'
  catatan: string
  batas_waktu_upload: string
}>({
  status_verifikasi: 'lengkap',
  catatan: '',
  batas_waktu_upload: '',
})

const isLocked = computed(() => {
  const s = target.value?.status_verifikasi
  return s === 'lengkap' || s === 'tercapai'
})

const canOperatorEdit = computed(() => {
  if (isLocked.value) return false
  if (isOperatorSatker.value && user.value?.satker_id !== target.value?.satker_id) return false
  return true
})

const canVerify = computed(() => {
  return isSuperAdmin.value || isAdminKanwil.value
})

function getStatusBadgeConfig(status?: string): { label: string; color: StatusColor } {
  switch (status) {
    case 'tercapai':
      return { label: 'Tercapai', color: 'blue' }
    case 'lengkap':
      return { label: 'Lengkap (Terkunci)', color: 'green' }
    case 'belum_verif':
      return { label: 'Menunggu Verifikasi', color: 'yellow' }
    case 'perlu_perbaikan':
      return { label: 'Perlu Perbaikan', color: 'red' }
    default:
      return { label: 'Belum Upload', color: 'gray' }
  }
}

function formatBytes(bytes: number): string {
  if (bytes < 1024) return bytes + ' B'
  const kb = bytes / 1024
  if (kb < 1024) return kb.toFixed(1) + ' KB'
  return (kb / 1024).toFixed(1) + ' MB'
}

function getFileUrl(path: string): string {
  if (path.startsWith('http')) return path
  return `http://127.0.0.1:8000/storage/${path}`
}

async function fetchWorkspace() {
  loading.value = true
  try {
    const res = await apiClient.post<{ data: RbTargetPeriode }>('/rb-target-periode/get-or-init', {
      rb_sub_indikator_id: subId,
      satker_id: satkerId,
      tahun_anggaran_id: tahunId,
      periode: periode,
    })
    target.value = res.data.data
    penjelasanZi.value = target.value.penjelasan_zi || ''
    if (target.value.batas_waktu_upload) {
      verifForm.batas_waktu_upload = target.value.batas_waktu_upload.substring(0, 16)
    }
  } catch (err: any) {
    toastError('Gagal memuat workspace', err.response?.data?.message)
    router.push('/lke')
  } finally {
    loading.value = false
  }
}

async function handleSavePenjelasan() {
  if (!target.value) return
  isSavingPenjelasan.value = true

  try {
    await apiClient.put(`/rb-target-periode/${target.value.id}`, {
      penjelasan_zi: penjelasanZi.value,
    })
    toastSuccess('Berhasil', 'Penjelasan ZI berhasil disimpan.')
    await fetchWorkspace()
  } catch (err: any) {
    toastError('Gagal menyimpan penjelasan', err.response?.data?.message)
  } finally {
    isSavingPenjelasan.value = false
  }
}

async function handleUploadDaduk(file: File) {
  if (!target.value) return
  isUploading.value = true

  try {
    const formData = new FormData()
    formData.append('rb_target_periode_id', String(target.value.id))
    formData.append('file', file)

    await apiClient.post('/rb-dokumen-daduk', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    toastSuccess('Berhasil', 'Dokumen data dukung PDF berhasil diunggah.')
    await fetchWorkspace()
  } catch (err: any) {
    toastError('Gagal mengunggah PDF', err.response?.data?.message)
  } finally {
    isUploading.value = false
  }
}

async function handleDeleteDaduk(docId: number) {
  if (!confirm('Hapus dokumen data dukung ini?')) return

  try {
    await apiClient.delete(`/rb-dokumen-daduk/${docId}`)
    toastSuccess('Berhasil', 'Dokumen data dukung berhasil dihapus.')
    await fetchWorkspace()
  } catch (err: any) {
    toastError('Gagal menghapus dokumen', err.response?.data?.message)
  }
}

async function handleSubmitDaduk() {
  if (!target.value) return
  if (!target.value.dokumen?.length) {
    toastError('Validasi Gagal', 'Wajib mengunggah minimal 1 berkas data dukung PDF sebelum mengajukan verifikasi.')
    return
  }

  if (!confirm('Ajukan seluruh data dukung pada poin ini ke Tim Kanwil untuk diverifikasi?')) return

  isSubmitting.value = true
  try {
    await apiClient.patch(`/rb-target-periode/${target.value.id}/submit`)
    toastSuccess('Berhasil', 'Data dukung diajukan ke Tim Verifikator Kanwil.')
    await fetchWorkspace()
  } catch (err: any) {
    toastError('Gagal mengajukan verifikasi', err.response?.data?.message)
  } finally {
    isSubmitting.value = false
  }
}

async function handleSendChat() {
  if (!target.value || !chatMessage.value.trim()) return

  isSendingChat.value = true
  try {
    await apiClient.post('/rb-catatan-verifikasi', {
      rb_target_periode_id: target.value.id,
      pesan: chatMessage.value.trim(),
    })
    chatMessage.value = ''
    await fetchWorkspace()
  } catch (err: any) {
    toastError('Gagal mengirim pesan', err.response?.data?.message)
  } finally {
    isSendingChat.value = false
  }
}

async function handleVerify() {
  if (!target.value) return
  isVerifying.value = true

  try {
    await apiClient.patch(`/rb-target-periode/${target.value.id}/verify`, {
      status_verifikasi: verifForm.status_verifikasi,
      catatan: verifForm.catatan,
    })

    if (verifForm.batas_waktu_upload) {
      await apiClient.put(`/rb-target-periode/${target.value.id}`, {
        batas_waktu_upload: verifForm.batas_waktu_upload,
      })
    }

    toastSuccess('Berhasil', 'Hasil verifikasi data dukung berhasil disimpan.')
    await fetchWorkspace()
  } catch (err: any) {
    toastError('Gagal menyimpan verifikasi', err.response?.data?.message)
  } finally {
    isVerifying.value = false
  }
}

onMounted(async () => {
  await fetchWorkspace()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in max-w-5xl mx-auto pb-16">
    <!-- Breadcrumb & Status -->
    <div class="flex items-center justify-between">
      <router-link
        to="/lke"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-kemenkum-navy transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Instrumen LKE
      </router-link>

      <StatusBadge
        v-if="target"
        :label="getStatusBadgeConfig(target.status_verifikasi).label"
        :color="getStatusBadgeConfig(target.status_verifikasi).color"
      />
    </div>

    <div v-if="loading" class="card p-12 text-center text-slate-400">
      <svg class="w-8 h-8 mx-auto animate-spin text-kemenkum-navy mb-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
      </svg>
      <p class="text-xs font-bold">Memuat workspace data dukung...</p>
    </div>

    <template v-else-if="target">
      <!-- Section 1: Sub Indikator Header Card -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gold text-slate-900">
                Area {{ target.sub_indikator?.indikator?.area?.kode }} &bull; {{ target.sub_indikator?.indikator?.kode }}
              </span>
              <span class="text-xs font-bold text-kemenkum-navy">Periode {{ target.periode }}</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 mt-1">
              Poin {{ target.sub_indikator?.nomor_poin }}: {{ target.sub_indikator?.judul_poin }}
            </h1>
          </div>

          <div class="text-left sm:text-right">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Satuan Kerja</span>
            <span class="text-xs font-bold text-slate-800">{{ target.satker?.nama }}</span>
            <span class="text-[10px] text-slate-400 block font-mono">{{ target.satker?.kode }}</span>
          </div>
        </div>

        <!-- Checklist Daduk Box -->
        <div class="p-4 bg-slate-50 rounded-xl space-y-1 text-xs border border-slate-100">
          <p class="font-bold text-slate-600 uppercase tracking-wider text-[10px]">Panduan & Checklist Data Dukung (Juknis Kemenkum RI)</p>
          <p class="text-slate-800 font-medium">
            {{ target.sub_indikator?.checklist_daduk || 'Lengkapi dokumen bukti sesuai petunjuk teknis LKE ZI 2026.' }}
          </p>
        </div>

        <!-- Deadline / Countdown widget -->
        <div
          v-if="target.batas_waktu_upload"
          class="flex items-center justify-between p-3 rounded-xl border text-xs"
          :class="target.countdown?.is_past ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-amber-50 border-amber-200 text-amber-800'"
        >
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-bold">Batas Waktu Unggah:</span>
            <span>{{ new Date(target.batas_waktu_upload).toLocaleString('id-ID') }}</span>
          </div>
          <span class="font-black text-xs">
            {{ target.countdown?.is_past ? 'Batas Waktu Berakhir' : `${target.countdown?.days} Hari ${target.countdown?.hours} Jam Tersisa` }}
          </span>
        </div>
      </div>

      <!-- Section 2: Lock Alert Banner -->
      <div
        v-if="isLocked"
        class="card p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-3"
      >
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <div>
          <p class="font-bold">Workspace Data Dukung Terkunci (Status: {{ target.status_verifikasi.toUpperCase() }})</p>
          <p class="text-emerald-700 mt-0.5">
            Dokumen data dukung telah diverifikasi sah oleh Tim Kanwil pada {{ target.verified_at ? new Date(target.verified_at).toLocaleString('id-ID') : '-' }}. Pengunggahan dan penghapusan dokumen telah dikunci secara otomatis.
          </p>
        </div>
      </div>

      <!-- Section 3: Penjelasan Isi ZI -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
          <h2 class="text-sm font-bold text-slate-800">Penjelasan Isi ZI</h2>
        </div>

        <div class="space-y-3 w-full">
          <textarea
            v-model="penjelasanZi"
            rows="5"
            :disabled="!canOperatorEdit"
            placeholder="Tuliskan uraian ringkas mengenai pemenuhan kriteria dan keterkaitan berkas bukti dukung..."
            class="block w-full rounded-xl border border-slate-200 p-3.5 text-xs text-slate-800 placeholder:text-slate-400 bg-white focus:border-kemenkum-navy focus:ring-1 focus:ring-kemenkum-navy focus:outline-none disabled:bg-slate-50 disabled:cursor-not-allowed resize-y min-h-[140px] leading-relaxed shadow-sm"
          ></textarea>

          <div v-if="canOperatorEdit" class="flex justify-end pt-1">
            <button
              type="button"
              class="btn-secondary text-xs inline-flex items-center gap-1.5 px-4 py-2 border-slate-300 hover:bg-slate-50 font-bold"
              :disabled="isSavingPenjelasan"
              @click="handleSavePenjelasan"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
              </svg>
              <span>{{ isSavingPenjelasan ? 'Menyimpan...' : 'Simpan Uraian' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Section 4: Data Dukung PDF Upload Zone (Maks. 50MB) -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-800">Dokumen Data Dukung (PDF Maks. 50MB)</h2>
            <p class="text-xs text-slate-400">Unggah berkas bukti dukung dalam format PDF standar nasional</p>
          </div>
          <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 font-bold text-slate-700">
            {{ target.dokumen?.length || 0 }} Berkas PDF
          </span>
        </div>

        <!-- Upload Dropzone (disabled if locked) -->
        <FileUploadZone
          v-if="canOperatorEdit"
          accept=".pdf"
          :max-size-mb="50"
          :disabled="isLocked"
          :uploading="isUploading"
          @select="handleUploadDaduk"
        />

        <!-- Files List -->
        <div v-if="target.dokumen?.length" class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
          <div
            v-for="file in target.dokumen"
            :key="file.id"
            class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="min-w-0">
                <a
                  :href="getFileUrl(file.path_file)"
                  target="_blank"
                  rel="noopener"
                  class="text-xs font-bold text-slate-800 hover:text-kemenkum-navy hover:underline truncate block"
                >
                  {{ file.nama_file }}
                </a>
                <p class="text-[10px] text-slate-400 mt-0.5">
                  {{ formatBytes(file.ukuran_file) }} &bull; Diunggah {{ new Date(file.created_at).toLocaleDateString('id-ID') }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
              <a
                :href="getFileUrl(file.path_file)"
                target="_blank"
                rel="noopener"
                class="btn-secondary py-1 px-2.5 text-[11px] inline-flex items-center gap-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Lihat / Unduh</span>
              </a>

              <button
                v-if="canOperatorEdit"
                class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors"
                title="Hapus berkas"
                @click="handleDeleteDaduk(file.id)"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-6 text-slate-400 text-xs">
          Belum ada berkas PDF data dukung yang diunggah untuk poin penilaian ini.
        </div>

        <!-- Submit for Verification Button (Operator) -->
        <div v-if="canOperatorEdit && target.dokumen?.length" class="pt-3 border-t border-slate-100 flex justify-end">
          <button
            type="button"
            class="btn-primary text-xs bg-emerald-700 hover:bg-emerald-800 inline-flex items-center gap-2"
            :disabled="isSubmitting"
            @click="handleSubmitDaduk"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>{{ isSubmitting ? 'Mengajukan...' : 'Ajukan Verifikasi Data Dukung ke Kanwil' }}</span>
          </button>
        </div>
      </div>

      <!-- Section 5: Two-Way Clarification Thread ("Ruang Diskusi & Klarifikasi") -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-slate-800">Ruang Klarifikasi & Diskusi Dua Arah</h2>
            <p class="text-xs text-slate-400">Komunikasi resmi antara Verifikator Kantor Wilayah dan Operator Satuan Kerja</p>
          </div>
          <span class="text-xs text-slate-500 font-bold">
            {{ target.catatan?.length || 0 }} Pesan
          </span>
        </div>

        <!-- Chat Stream (Natural Flow) -->
        <div class="space-y-3 p-4 bg-slate-50/70 rounded-xl border border-slate-100">
          <div v-if="!target.catatan?.length" class="text-center py-6 text-xs text-slate-400">
            Belum ada pesan klarifikasi pada poin penilaian ini.
          </div>

          <div
            v-for="c in target.catatan"
            :key="c.id"
            class="flex flex-col space-y-1 text-xs"
            :class="c.sender_id === user?.id ? 'items-end' : 'items-start'"
          >
            <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
              <span class="font-bold text-slate-700">{{ c.sender?.name }}</span>
              <span>&bull;</span>
              <span>{{ new Date(c.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</span>
            </div>
            <div
              class="max-w-md rounded-2xl px-4 py-2.5 shadow-sm"
              :class="c.sender_id === user?.id
                ? 'bg-kemenkum-navy text-white rounded-br-none'
                : 'bg-white text-slate-800 border border-slate-200 rounded-bl-none'"
            >
              <p class="leading-relaxed">{{ c.pesan }}</p>
            </div>
          </div>
        </div>

        <!-- Send Message Form -->
        <form class="flex items-center gap-2 pt-1" @submit.prevent="handleSendChat">
          <input
            v-model="chatMessage"
            type="text"
            placeholder="Tulis tanggapan atau pertanyaan klarifikasi..."
            class="input-text text-xs py-2 flex-1"
            :disabled="isSendingChat"
          />
          <button
            type="submit"
            class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5"
            :disabled="isSendingChat || !chatMessage.trim()"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            <span>Kirim</span>
          </button>
        </form>
      </div>

      <!-- Section 6: Panel Verifikator Kanwil (Admin Kanwil & Super Admin only) -->
      <div
        v-if="canVerify"
        class="card p-6 bg-slate-900 text-white border border-slate-800 space-y-4"
      >
        <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
          <div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gold text-slate-900">
              Hak Akses Verifikator Tim ZI
            </span>
            <h2 class="text-sm font-bold text-white mt-1">Panel Verifikasi Kantor Wilayah</h2>
          </div>
          <span class="text-xs text-slate-400">Verifikator: {{ user?.name }}</span>
        </div>

        <div class="space-y-4 text-xs">
          <!-- Status Radio Options -->
          <div>
            <label class="block font-bold text-slate-300 mb-2">Keputusan Status Verifikasi</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <label class="p-3 rounded-xl border border-slate-700 bg-slate-800/80 cursor-pointer flex items-center gap-2.5">
                <input
                  v-model="verifForm.status_verifikasi"
                  type="radio"
                  value="lengkap"
                  class="accent-emerald-500"
                />
                <div>
                  <span class="font-bold text-emerald-400 block">Lengkap (Kunci)</span>
                  <span class="text-[10px] text-slate-400">Kunci data dukung</span>
                </div>
              </label>

              <label class="p-3 rounded-xl border border-slate-700 bg-slate-800/80 cursor-pointer flex items-center gap-2.5">
                <input
                  v-model="verifForm.status_verifikasi"
                  type="radio"
                  value="perlu_perbaikan"
                  class="accent-rose-500"
                />
                <div>
                  <span class="font-bold text-rose-400 block">Perlu Perbaikan</span>
                  <span class="text-[10px] text-slate-400">Kembalikan ke Satker</span>
                </div>
              </label>

              <label class="p-3 rounded-xl border border-slate-700 bg-slate-800/80 cursor-pointer flex items-center gap-2.5">
                <input
                  v-model="verifForm.status_verifikasi"
                  type="radio"
                  value="tercapai"
                  class="accent-blue-500"
                />
                <div>
                  <span class="font-bold text-blue-400 block">Tercapai</span>
                  <span class="text-[10px] text-slate-400">Target terpenuhi penuh</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Deadline input -->
          <div>
            <label class="block font-bold text-slate-300 mb-1">Set / Perpanjang Batas Waktu Unggah</label>
            <input
              v-model="verifForm.batas_waktu_upload"
              type="datetime-local"
              class="rounded-xl bg-slate-800 border border-slate-700 text-slate-100 text-xs p-2 focus:outline-none focus:border-gold"
            />
          </div>

          <!-- Catatan / Umpan Balik -->
          <div>
            <label class="block font-bold text-slate-300 mb-1">
              Catatan Tim Verifikator (Otomatis masuk ke ruang diskusi)
            </label>
            <textarea
              v-model="verifForm.catatan"
              rows="3"
              placeholder="Berikan arahan kelengkapan atau hasil penilaian bukti dukung..."
              class="w-full rounded-xl bg-slate-800 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs p-3 focus:outline-none focus:border-gold resize-none"
            ></textarea>
          </div>

          <div class="flex justify-end pt-2">
            <button
              type="button"
              class="btn-primary text-xs bg-gold text-slate-950 hover:bg-gold-light inline-flex items-center gap-2 font-bold"
              :disabled="isVerifying"
              @click="handleVerify"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ isVerifying ? 'Menyimpan...' : 'Simpan Keputusan Verifikasi' }}</span>
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>