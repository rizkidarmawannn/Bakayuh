<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { RencanaAksi, StatusColor, BuktiDukungRenaksi } from '@/types'
import StatusBadge from '@/components/common/StatusBadge.vue'
import FileUploadZone from '@/components/common/FileUploadZone.vue'

const route = useRoute()
const router = useRouter()
const { isSuperAdmin, isAdminKanwil, isOperatorSatker, user } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const renaksiId = Number(route.params.id)
const loading = ref(true)
const renaksi = ref<RencanaAksi | null>(null)

// Realisasi Form
const realisasiForm = reactive({
  deskripsi_realisasi: '',
  persentase_selesai: 0,
})
const isSavingDraft = ref(false)
const isSubmitting = ref(false)
const isUploading = ref(false)

// Verifikator Form
const isVerifying = ref(false)
const verifForm = reactive<{
  status: 'terverifikasi' | 'perlu_perbaikan'
  catatan_verifikasi: string
}>({
  status: 'terverifikasi',
  catatan_verifikasi: '',
})

// Permissions & Editability
const isEditable = computed(() => {
  if (!renaksi.value?.realisasi) return true // Belum ada realisasi, operator can draft
  const s = renaksi.value.realisasi.status
  return s === 'belum_lapor' || s === 'perlu_perbaikan'
})

const canOperatorEdit = computed(() => {
  if (!isOperatorSatker.value && !isSuperAdmin.value) return false
  if (isOperatorSatker.value && user.value?.satker_id !== renaksi.value?.satker_id) return false
  return isEditable.value
})

const canVerify = computed(() => {
  return isSuperAdmin.value || isAdminKanwil.value
})

const currentStatus = computed(() => {
  return renaksi.value?.realisasi?.status ?? 'belum_lapor'
})

function getStatusBadgeConfig(status: string): { label: string; color: StatusColor } {
  switch (status) {
    case 'terverifikasi':
      return { label: 'Terverifikasi', color: 'green' }
    case 'menunggu_verifikasi':
      return { label: 'Menunggu Verifikasi', color: 'yellow' }
    case 'perlu_perbaikan':
      return { label: 'Perlu Perbaikan', color: 'red' }
    default:
      return { label: 'Belum Lapor', color: 'gray' }
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

async function fetchDetail() {
  loading.value = true
  try {
    const res = await apiClient.get<{ data: RencanaAksi }>(`/rencana-aksi/${renaksiId}`)
    renaksi.value = res.data.data

    if (renaksi.value.realisasi) {
      realisasiForm.deskripsi_realisasi = renaksi.value.realisasi.deskripsi_realisasi || ''
      realisasiForm.persentase_selesai = renaksi.value.realisasi.persentase_selesai || 0
      verifForm.catatan_verifikasi = renaksi.value.realisasi.catatan_verifikasi || ''
    }
  } catch (err: any) {
    toastError('Gagal memuat detail', err.response?.data?.message)
    router.push('/renaksi')
  } finally {
    loading.value = false
  }
}

async function handleSaveRealisasi() {
  if (!realisasiForm.deskripsi_realisasi.trim()) {
    toastError('Validasi Gagal', 'Deskripsi realisasi pelaksanaan wajib diisi.')
    return
  }

  isSavingDraft.value = true
  try {
    if (renaksi.value?.realisasi?.id) {
      // Update
      await apiClient.put(`/realisasi-renaksi/${renaksi.value.realisasi.id}`, {
        deskripsi_realisasi: realisasiForm.deskripsi_realisasi,
        persentase_selesai: realisasiForm.persentase_selesai,
      })
    } else {
      // Create
      await apiClient.post('/realisasi-renaksi', {
        rencana_aksi_id: renaksiId,
        deskripsi_realisasi: realisasiForm.deskripsi_realisasi,
        persentase_selesai: realisasiForm.persentase_selesai,
      })
    }
    toastSuccess('Berhasil', 'Draf realisasi berhasil disimpan.')
    await fetchDetail()
  } catch (err: any) {
    toastError('Gagal menyimpan draf', err.response?.data?.message)
  } finally {
    isSavingDraft.value = false
  }
}

async function handleFileUpload(file: File) {
  if (!renaksi.value?.realisasi?.id) {
    toastError('Perhatian', 'Harap simpan Draf Realisasi terlebih dahulu sebelum mengunggah berkas bukti.')
    return
  }

  isUploading.value = true
  try {
    const formData = new FormData()
    formData.append('realisasi_renaksi_id', String(renaksi.value.realisasi.id))
    formData.append('file', file)

    await apiClient.post('/bukti-dukung-renaksi', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    toastSuccess('Berhasil', 'Dokumen bukti dukung berhasil diunggah.')
    await fetchDetail()
  } catch (err: any) {
    toastError('Gagal mengunggah', err.response?.data?.message)
  } finally {
    isUploading.value = false
  }
}

async function handleDeleteBukti(buktiId: number) {
  if (!confirm('Hapus dokumen bukti dukung ini?')) return

  try {
    await apiClient.delete(`/bukti-dukung-renaksi/${buktiId}`)
    toastSuccess('Berhasil', 'Dokumen bukti dukung berhasil dihapus.')
    await fetchDetail()
  } catch (err: any) {
    toastError('Gagal menghapus', err.response?.data?.message)
  }
}

async function handleSubmitForVerification() {
  if (!renaksi.value?.realisasi?.id) {
    toastError('Validasi Gagal', 'Harap simpan draf laporan terlebih dahulu.')
    return
  }

  if (!renaksi.value.realisasi.bukti_dukung?.length) {
    toastError('Validasi Gagal', 'Wajib mengunggah minimal 1 dokumen bukti dukung sebelum mengajukan verifikasi.')
    return
  }

  if (!confirm('Ajukan laporan realisasi rencana aksi ini ke Kanwil untuk diverifikasi?')) return

  isSubmitting.value = true
  try {
    await apiClient.patch(`/realisasi-renaksi/${renaksi.value.realisasi.id}/submit`)
    toastSuccess('Berhasil', 'Laporan realisasi berhasil diajukan untuk verifikasi Kanwil.')
    await fetchDetail()
  } catch (err: any) {
    toastError('Gagal mengajukan verifikasi', err.response?.data?.message)
  } finally {
    isSubmitting.value = false
  }
}

async function handleVerify() {
  if (!renaksi.value?.realisasi?.id) return

  if (verifForm.status === 'perlu_perbaikan' && !verifForm.catatan_verifikasi.trim()) {
    toastError('Validasi', 'Wajib menyertakan catatan perbaikan untuk satker.')
    return
  }

  isVerifying.value = true
  try {
    await apiClient.patch(`/realisasi-renaksi/${renaksi.value.realisasi.id}/verify`, verifForm)
    toastSuccess('Berhasil', 'Status verifikasi realisasi berhasil diperbarui.')
    await fetchDetail()
  } catch (err: any) {
    toastError('Gagal verifikasi', err.response?.data?.message)
  } finally {
    isVerifying.value = false
  }
}

onMounted(async () => {
  await fetchDetail()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in max-w-5xl mx-auto pb-12">
    <!-- Back & Breadcrumb -->
    <div class="flex items-center justify-between">
      <router-link
        to="/renaksi"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-kemenkum-navy transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Daftar Renaksi
      </router-link>

      <StatusBadge
        v-if="renaksi"
        :label="getStatusBadgeConfig(currentStatus).label"
        :color="getStatusBadgeConfig(currentStatus).color"
      />
    </div>

    <div v-if="loading" class="card p-12 text-center text-slate-400">
      <svg class="w-8 h-8 mx-auto animate-spin text-kemenkum-navy mb-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
      </svg>
      <p class="text-xs font-bold">Memuat rincian rencana aksi...</p>
    </div>

    <template v-else-if="renaksi">
      <!-- Section 1: Informasi Rencana Aksi -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[11px] font-black tracking-wider bg-kemenkum-navy text-white">
                {{ renaksi.triwulan }}
              </span>
              <span class="text-xs text-slate-400">
                Tahun Anggaran {{ renaksi.tahun_anggaran?.tahun }}
              </span>
            </div>
            <h1 class="text-lg sm:text-xl font-black text-slate-900 mt-1">
              {{ renaksi.nama_aksi }}
            </h1>
          </div>
          <div class="text-left sm:text-right">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Satuan Kerja</span>
            <span class="text-xs font-bold text-slate-800">{{ renaksi.satker?.nama }}</span>
            <span class="text-[10px] text-slate-400 block font-mono">{{ renaksi.satker?.kode }}</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl space-y-1">
            <p class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Indikator Kinerja Sasaran</p>
            <p class="font-semibold text-slate-800">{{ renaksi.indikator?.nama }}</p>
            <p class="text-[10px] text-slate-400 font-mono">Kode: {{ renaksi.indikator?.kode }} (Satuan: {{ renaksi.indikator?.satuan }})</p>
          </div>

          <div class="p-3 bg-slate-50 rounded-xl space-y-1">
            <p class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Target Output / Tolok Ukur</p>
            <p class="font-semibold text-slate-800">{{ renaksi.target_output || 'Tidak ada target output spesifik' }}</p>
          </div>
        </div>
      </div>

      <!-- Section 2: Banner Status & Verifikasi Notes -->
      <div
        v-if="currentStatus === 'menunggu_verifikasi'"
        class="card p-4 bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-3"
      >
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-bold">Laporan Sedang Menunggu Verifikasi Tim Kanwil</p>
          <p class="text-amber-700 mt-0.5">
            Laporan telah dikirim pada {{ renaksi.realisasi?.submitted_at ? new Date(renaksi.realisasi.submitted_at).toLocaleString('id-ID') : '-' }}. Formulir pelaporan sementara dikunci hingga proses verifikasi selesai.
          </p>
        </div>
      </div>

      <div
        v-else-if="currentStatus === 'perlu_perbaikan'"
        class="card p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3"
      >
        <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="space-y-1">
          <p class="font-bold">Laporan Memerlukan Perbaikan</p>
          <p class="text-rose-700">
            <span class="font-semibold">Catatan Tim Verifikator:</span> {{ renaksi.realisasi?.catatan_verifikasi || 'Mohon lengkapi dokumen atau rincian pelaksanaan.' }}
          </p>
          <p class="text-[11px] text-rose-600">
            Silakan sesuaikan uraian dan unggah dokumen bukti pendukung tambahan, lalu ajukan kembali.
          </p>
        </div>
      </div>

      <div
        v-else-if="currentStatus === 'terverifikasi'"
        class="card p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-3"
      >
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-bold">Laporan Realisasi Terverifikasi Sah</p>
          <p class="text-emerald-700 mt-0.5">
            Diverifikasi oleh Tim Kanwil pada {{ renaksi.realisasi?.verified_at ? new Date(renaksi.realisasi.verified_at).toLocaleString('id-ID') : '-' }}.
            <span v-if="renaksi.realisasi?.catatan_verifikasi" class="block font-semibold mt-1">Catatan: {{ renaksi.realisasi.catatan_verifikasi }}</span>
          </p>
        </div>
      </div>

      <!-- Section 3: Form Realisasi Pelaksanaan -->
      <div class="card p-6 bg-white border border-slate-200 space-y-5">
        <div class="border-b border-slate-100 pb-3">
          <h2 class="text-sm font-bold text-slate-800">Pelaporan Capaian & Realisasi</h2>
          <p class="text-xs text-slate-400">Deskripsikan progres pelaksanaan kegiatan dan persentase ketercapaian</p>
        </div>

        <div class="space-y-4">
          <!-- Deskripsi Realisasi -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">
              Uraian Realisasi Pelaksanaan <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="realisasiForm.deskripsi_realisasi"
              rows="4"
              :disabled="!canOperatorEdit"
              placeholder="Jelaskan pelaksanaan kegiatan, hasil yang dicapai, kendala dan tindak lanjut..."
              class="input-text text-xs resize-none"
              :class="{ 'bg-slate-50 cursor-not-allowed': !canOperatorEdit }"
            ></textarea>
          </div>

          <!-- Slider / Number Persentase Selesai -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs font-bold text-slate-700">Progres Ketercapaian Output (%)</label>
              <span class="text-sm font-black text-kemenkum-navy">{{ realisasiForm.persentase_selesai }}%</span>
            </div>
            <div class="flex items-center gap-4">
              <input
                v-model.number="realisasiForm.persentase_selesai"
                type="range"
                min="0"
                max="100"
                step="5"
                :disabled="!canOperatorEdit"
                class="w-full accent-kemenkum-navy cursor-pointer disabled:cursor-not-allowed"
              />
              <input
                v-model.number="realisasiForm.persentase_selesai"
                type="number"
                min="0"
                max="100"
                :disabled="!canOperatorEdit"
                class="input-text text-xs py-1.5 w-20 text-center font-bold"
                :class="{ 'bg-slate-50 cursor-not-allowed': !canOperatorEdit }"
              />
            </div>
          </div>

          <!-- Action Buttons for Operator -->
          <div v-if="canOperatorEdit" class="flex flex-wrap items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              class="btn-secondary text-xs inline-flex items-center gap-1.5"
              :disabled="isSavingDraft"
              @click="handleSaveRealisasi"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
              </svg>
              <span>{{ isSavingDraft ? 'Menyimpan...' : 'Simpan Draf' }}</span>
            </button>

            <button
              v-if="renaksi.realisasi?.id"
              type="button"
              class="btn-primary text-xs inline-flex items-center gap-1.5 bg-emerald-700 hover:bg-emerald-800"
              :disabled="isSubmitting || !renaksi.realisasi?.bukti_dukung?.length"
              :title="!renaksi.realisasi?.bukti_dukung?.length ? 'Wajib unggah bukti sebelum submit' : ''"
              @click="handleSubmitForVerification"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span>{{ isSubmitting ? 'Mengajukan...' : 'Ajukan Verifikasi ke Kanwil' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Section 4: Dokumen Bukti Dukung (Maks. 10MB) -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-800">Dokumen Bukti Dukung</h2>
            <p class="text-xs text-slate-400">Berkas pendukung capaian output (PDF, JPG, PNG maks. 10MB)</p>
          </div>
          <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 font-bold text-slate-700">
            {{ renaksi.realisasi?.bukti_dukung?.length || 0 }} Berkas Terunggah
          </span>
        </div>

        <!-- Upload Dropzone (only if editable & draft saved) -->
        <div v-if="canOperatorEdit">
          <div v-if="!renaksi.realisasi?.id" class="p-4 bg-amber-50 rounded-xl text-xs text-amber-800 border border-amber-200">
            Harap simpan formulir realisasi di atas terlebih dahulu untuk membuka pengunggahan berkas bukti dukung.
          </div>
          <FileUploadZone
            v-else
            :max-size-mb="10"
            :uploading="isUploading"
            @select="handleFileUpload"
          />
        </div>

        <!-- Uploaded Files List -->
        <div v-if="renaksi.realisasi?.bukti_dukung?.length" class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
          <div
            v-for="file in renaksi.realisasi.bukti_dukung"
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
                <span>Unduh</span>
              </a>

              <button
                v-if="canOperatorEdit"
                class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors"
                title="Hapus berkas"
                @click="handleDeleteBukti(file.id)"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-6 text-slate-400 text-xs">
          Belum ada berkas bukti dukung yang dilampirkan.
        </div>
      </div>

      <!-- Section 5: Panel Verifikasi Kanwil (Admin Kanwil & Super Admin only) -->
      <div
        v-if="canVerify"
        class="card p-6 bg-slate-900 text-white border border-slate-800 space-y-4"
      >
        <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
          <div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gold text-slate-900">
              Hak Akses Verifikator
            </span>
            <h2 class="text-sm font-bold text-white mt-1">Panel Verifikasi Kantor Wilayah</h2>
          </div>
          <span class="text-xs text-slate-400">
            Penilai: {{ user?.name }}
          </span>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-2">Keputusan Verifikasi</label>
            <div class="flex items-center gap-4">
              <label class="flex items-center gap-2 cursor-pointer text-xs">
                <input
                  v-model="verifForm.status"
                  type="radio"
                  value="terverifikasi"
                  class="accent-emerald-500"
                />
                <span class="text-emerald-400 font-bold">Terverifikasi (Setujui)</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer text-xs">
                <input
                  v-model="verifForm.status"
                  type="radio"
                  value="perlu_perbaikan"
                  class="accent-rose-500"
                />
                <span class="text-rose-400 font-bold">Perlu Perbaikan (Kembalikan ke Satker)</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">
              Catatan Verifikasi / Umpan Balik Tim Penilai
              <span v-if="verifForm.status === 'perlu_perbaikan'" class="text-rose-400">*</span>
            </label>
            <textarea
              v-model="verifForm.catatan_verifikasi"
              rows="3"
              placeholder="Tuliskan catatan perbaikan atau apresiasi hasil capaian..."
              class="w-full rounded-xl bg-slate-800 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs p-3 focus:outline-none focus:border-gold resize-none"
            ></textarea>
          </div>

          <div class="flex justify-end pt-2">
            <button
              type="button"
              class="btn-primary text-xs bg-gold text-slate-950 hover:bg-gold-light inline-flex items-center gap-2 font-bold"
              :disabled="isVerifying || !renaksi.realisasi?.id"
              @click="handleVerify"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ isVerifying ? 'Menyimpan Verifikasi...' : 'Simpan Keputusan Verifikasi' }}</span>
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>