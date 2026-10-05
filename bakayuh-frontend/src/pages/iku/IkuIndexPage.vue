<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { TargetIku, SatuanKerja, IndikatorKinerja, TahunAnggaran } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const { isSuperAdmin, isAdminKanwil, isOperatorSatker, user } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const targets = ref<TargetIku[]>([])
const satkers = ref<SatuanKerja[]>([])
const indikators = ref<IndikatorKinerja[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const selectedSatkerId = ref<number | null>(null)
const selectedTahunId = ref<number | null>(null)

const searchQuery = ref('')

const filteredTargets = computed(() => {
  if (!searchQuery.value.trim()) return targets.value
  const q = searchQuery.value.toLowerCase()
  return targets.value.filter(
    (t) =>
      (t.indikator?.nama?.toLowerCase().includes(q) ?? false) ||
      (t.indikator?.kode?.toLowerCase().includes(q) ?? false) ||
      (t.satker?.nama?.toLowerCase().includes(q) ?? false)
  )
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedTargets,
} = usePagination(filteredTargets, { defaultPageSize: 10 })

// Modal State
const targetModalOpen = ref(false)
const realisasiModalOpen = ref(false)
const modalSubmitting = ref(false)

const targetForm = reactive({
  tahun_anggaran_id: 0,
  satker_id: 0,
  indikator_id: 0,
  nilai_target: 0,
})

const realisasiForm = reactive({
  target_iku_id: 0,
  nilai_realisasi: 0,
  keterangan: '',
})

const activeTargetForRealisasi = ref<TargetIku | null>(null)

// Live calculation preview in modal
const livePersentase = computed(() => {
  if (!activeTargetForRealisasi.value || !activeTargetForRealisasi.value.nilai_target) return 0
  const t = Number(activeTargetForRealisasi.value.nilai_target)
  const r = Number(realisasiForm.nilai_realisasi)
  if (t <= 0) return 0
  return Number(((r / t) * 100).toFixed(2))
})

async function loadInitialData() {
  try {
    const [resSatker, resInd, resTahun] = await Promise.all([
      apiClient.get<{ data: SatuanKerja[] }>('/satker'),
      apiClient.get<{ data: IndikatorKinerja[] }>('/indikator-kinerja'),
      apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran'),
    ])
    satkers.value = resSatker.data.data
    indikators.value = resInd.data.data
    tahuns.value = resTahun.data.data

    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif) selectedTahunId.value = aktif.id

    // Operator satker default to own satker
    if (isOperatorSatker.value && user.value?.satker_id) {
      selectedSatkerId.value = user.value.satker_id
    }
  } catch (err) {
    console.error(err)
  }
}

async function fetchTargets() {
  loading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    if (selectedSatkerId.value) params.satker_id = selectedSatkerId.value

    const res = await apiClient.get<{ data: TargetIku[] }>('/target-iku', { params })
    targets.value = res.data.data
  } catch (err: any) {
    toastError('Gagal memuat data', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openTargetModal() {
  targetForm.tahun_anggaran_id = selectedTahunId.value ?? (tahuns.value[0]?.id || 0)
  targetForm.satker_id = selectedSatkerId.value ?? (satkers.value[0]?.id || 0)
  targetForm.indikator_id = indikators.value[0]?.id || 0
  targetForm.nilai_target = 100
  targetModalOpen.value = true
}

async function saveTarget() {
  modalSubmitting.value = true
  try {
    await apiClient.post('/target-iku', targetForm)
    toastSuccess('Berhasil', 'Target IKU berhasil disimpan.')
    targetModalOpen.value = false
    await fetchTargets()
  } catch (err: any) {
    toastError('Gagal menyimpan target', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

function openRealisasiModal(target: TargetIku) {
  activeTargetForRealisasi.value = target
  realisasiForm.target_iku_id = target.id
  realisasiForm.nilai_realisasi = target.realisasi ? Number(target.realisasi.nilai_realisasi) : 0
  realisasiForm.keterangan = target.realisasi?.keterangan ?? ''
  realisasiModalOpen.value = true
}

async function saveRealisasi() {
  modalSubmitting.value = true
  try {
    await apiClient.post('/realisasi-iku', realisasiForm)
    toastSuccess('Berhasil', 'Realisasi IKU berhasil disimpan.')
    realisasiModalOpen.value = false
    await fetchTargets()
  } catch (err: any) {
    toastError('Gagal menyimpan realisasi', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

onMounted(async () => {
  await loadInitialData()
  await fetchTargets()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Official Kemenkumham Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-2">
        <div class="flex items-center gap-2 text-xs text-slate-300 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Indikator Kinerja Utama</span>
        </div>
        <div class="text-[11px] font-black uppercase tracking-widest text-amber-400">
          Indikator Kinerja
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-slate-900 shadow-sm shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-13h-2v6h6v-2h-4z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide uppercase">
                Capaian Indikator Kinerja
              </h1>
              <p class="text-xs sm:text-sm text-slate-200 mt-0.5 max-w-2xl leading-relaxed">
                Pengelolaan target tahunan dan pelaporan realisasi IKU Satuan Kerja se-Kalimantan Selatan.
              </p>
            </div>
          </div>

          <!-- Action Buttons in Banner -->
          <div class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <RouterLink
              to="/iku/matriks"
              class="px-3.5 py-2 rounded-xl border border-white/20 text-xs font-bold text-white hover:bg-white/10 transition-all flex items-center gap-1.5 backdrop-blur-xs"
            >
              <span>Lihat Matriks</span>
              <span class="text-amber-400">&rarr;</span>
            </RouterLink>

            <button
              v-if="isSuperAdmin || isAdminKanwil"
              type="button"
              @click="openTargetModal"
              class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all border border-amber-400/40 bg-amber-500 text-slate-950 hover:bg-amber-400 flex items-center gap-1.5 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Tetapkan Target IKU</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm space-y-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Tahun Anggaran -->
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Tahun Anggaran:</label>
            <select
              v-model="selectedTahunId"
              @change="fetchTargets"
              class="input-select text-xs py-2 pl-3 pr-8 font-semibold border-slate-200 rounded-xl focus:ring-2 focus:ring-[#091F4A]"
            >
              <option v-for="t in tahuns" :key="t.id" :value="t.id">
                {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
              </option>
            </select>
          </div>

          <!-- Satuan Kerja -->
          <div v-if="isSuperAdmin || isAdminKanwil" class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Satuan Kerja:</label>
            <select
              v-model="selectedSatkerId"
              @change="fetchTargets"
              class="input-select text-xs py-2 pl-3 pr-8 font-semibold border-slate-200 rounded-xl max-w-xs focus:ring-2 focus:ring-[#091F4A]"
            >
              <option :value="null">Semua Satuan Kerja ({{ satkers.length }})</option>
              <option v-for="s in satkers" :key="s.id" :value="s.id">
                {{ s.nama }}
              </option>
            </select>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="w-full md:w-72">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari indikator atau satker..."
              class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#091F4A]"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card overflow-hidden bg-white border border-slate-200 shadow-sm rounded-xl">
      <!-- Card Title Bar -->
      <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <span class="px-2.5 py-1 rounded bg-[#091F4A] text-white text-[10px] font-black uppercase tracking-wider">
            IKU
          </span>
          <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
            Capaian Indikator Kinerja Utama
          </h2>
        </div>
        <div class="text-xs font-semibold text-slate-500">
          Total: <span class="font-bold text-slate-800">{{ filteredTargets.length }}</span> Indikator
        </div>
      </div>

      <SkeletonTable v-if="loading" :rows="6" :cols="7" />

      <div v-else-if="filteredTargets.length === 0" class="p-16 text-center text-slate-400">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="font-bold text-sm text-slate-700">Tidak ada data Target IKU</p>
        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter atau tetapkan target indikator baru.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-black uppercase tracking-wider">
              <th class="py-4 px-4 text-center w-12 border-r border-white/10">#</th>
              <th class="py-4 px-4 text-center w-28 border-r border-white/10">Kode</th>
              <th class="py-4 px-5 min-w-[320px] border-r border-white/10">Sasaran & Indikator Kinerja</th>
              <th class="py-4 px-5 min-w-[240px] border-r border-white/10">Satuan Kerja</th>
              <th class="py-4 px-4 text-right w-28 border-r border-white/10">Target</th>
              <th class="py-4 px-3 text-center w-24 border-r border-white/10">Satuan</th>
              <th class="py-4 px-4 text-right w-28 border-r border-white/10">Realisasi</th>
              <th class="py-4 px-4 text-center w-32 border-r border-white/10">Capaian</th>
              <th class="py-4 px-4 text-center w-32">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr
              v-for="(t, idx) in paginatedTargets"
              :key="t.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <!-- No -->
              <td class="py-4 px-4 text-center font-bold text-slate-400">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>

              <!-- Kode -->
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-2.5 py-1 rounded bg-[#091F4A]/10 text-[#091F4A] font-black font-mono text-[11px] border border-[#091F4A]/20">
                  {{ t.indikator?.kode || '-' }}
                </span>
              </td>

              <!-- Indikator -->
              <td class="py-4 px-5">
                <div class="flex items-start gap-2.5">
                  <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                    🎯
                  </div>
                  <div>
                    <span class="font-bold text-slate-900 leading-snug block">
                      {{ t.indikator?.nama }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono mt-0.5 inline-block">
                      Tingkat: {{ t.indikator?.level || 'Program' }} &bull; Polaritas: {{ t.indikator?.polaritas || 'Positif' }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Satker -->
              <td class="py-4 px-5">
                <div class="font-semibold text-slate-800 leading-snug">
                  {{ t.satker?.nama || '-' }}
                </div>
                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                  {{ t.satker?.kode }} &bull; {{ t.satker?.tipe?.toUpperCase() }}
                </div>
              </td>

              <!-- Target -->
              <td class="py-4 px-4 text-right font-mono font-bold text-slate-800">
                {{ Number(t.nilai_target).toFixed(2) }}
              </td>

              <!-- Satuan -->
              <td class="py-4 px-3 text-center text-slate-600 font-medium">
                {{ t.indikator?.satuan || '%' }}
              </td>

              <!-- Realisasi -->
              <td class="py-4 px-4 text-right font-mono font-bold">
                <span v-if="t.realisasi" class="text-slate-800">
                  {{ Number(t.realisasi.nilai_realisasi).toFixed(2) }}
                </span>
                <span v-else class="text-slate-300">&mdash;</span>
              </td>

              <!-- Capaian -->
              <td class="py-4 px-4 text-center">
                <div v-if="t.realisasi" class="inline-flex items-center gap-1.5">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-black shadow-xs"
                    :class="{
                      'bg-emerald-100 text-emerald-800 border border-emerald-300': Number(t.realisasi.persentase_capaian) >= 100,
                      'bg-amber-100 text-amber-800 border border-amber-300': Number(t.realisasi.persentase_capaian) >= 80 && Number(t.realisasi.persentase_capaian) < 100,
                      'bg-rose-100 text-rose-800 border border-rose-300': Number(t.realisasi.persentase_capaian) < 80,
                    }"
                  >
                    {{ Number(t.realisasi.persentase_capaian).toFixed(1) }}%
                  </span>
                </div>
                <span v-else class="text-slate-400 text-xs italic">Belum lapor</span>
              </td>

              <!-- Aksi -->
              <td class="py-4 px-4 text-center">
                <button
                  type="button"
                  @click="openRealisasiModal(t)"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-xs"
                >
                  {{ t.realisasi ? 'Ubah Realisasi' : 'Input Realisasi' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <AppPagination
        v-if="filteredTargets.length > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total-items="totalItems"
      />
    </div>

    <!-- Modal Target IKU (Admin) -->
    <div v-if="targetModalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="card max-w-lg w-full p-6 animate-slide-in">
        <h3 class="text-base font-bold text-slate-900 mb-1">Tetapkan Target IKU</h3>
        <p class="text-xs text-slate-500 mb-4">Penetapan target indikator kinerja satuan kerja</p>

        <form @submit.prevent="saveTarget" class="space-y-3.5">
          <div>
            <label class="form-label text-xs">Tahun Anggaran</label>
            <select v-model="targetForm.tahun_anggaran_id" class="form-select text-xs" required>
              <option v-for="th in tahuns" :key="th.id" :value="th.id">Tahun {{ th.tahun }}</option>
            </select>
          </div>

          <div>
            <label class="form-label text-xs">Satuan Kerja</label>
            <select v-model="targetForm.satker_id" class="form-select text-xs" required>
              <option v-for="sk in satkers" :key="sk.id" :value="sk.id">{{ sk.nama }}</option>
            </select>
          </div>

          <div>
            <label class="form-label text-xs">Indikator Kinerja Utama</label>
            <select v-model="targetForm.indikator_id" class="form-select text-xs" required>
              <option v-for="ik in indikators" :key="ik.id" :value="ik.id">
                [{{ ik.kode }}] {{ ik.nama }} ({{ ik.satuan }})
              </option>
            </select>
          </div>

          <div>
            <label class="form-label text-xs">Nilai Target</label>
            <input
              v-model.number="targetForm.nilai_target"
              type="number"
              step="0.01"
              required
              class="form-input text-xs"
              placeholder="100.00"
            />
          </div>

          <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="targetModalOpen = false" class="btn-secondary text-xs">Batal</button>
            <button type="submit" class="btn-primary text-xs" :disabled="modalSubmitting">
              {{ modalSubmitting ? 'Menyimpan...' : 'Simpan Target' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Realisasi IKU (Operator) -->
    <div v-if="realisasiModalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="card max-w-md w-full p-6 animate-slide-in">
        <h3 class="text-base font-bold text-slate-900 mb-1">Input Realisasi Capaian IKU</h3>
        <p class="text-xs text-slate-500 mb-3 truncate">
          {{ activeTargetForRealisasi?.indikator?.nama }}
        </p>

        <div class="card p-3 bg-slate-50 mb-4 border border-slate-200 text-xs">
          <div class="flex justify-between text-slate-600">
            <span>Nilai Target:</span>
            <span class="font-bold text-slate-800">{{ activeTargetForRealisasi?.nilai_target }} {{ activeTargetForRealisasi?.indikator?.satuan }}</span>
          </div>
          <div class="flex justify-between text-slate-600 mt-1">
            <span>Estimasi Capaian:</span>
            <span class="font-bold" :class="livePersentase >= 100 ? 'text-green-600' : livePersentase >= 80 ? 'text-yellow-600' : 'text-red-600'">
              {{ livePersentase }}%
            </span>
          </div>
        </div>

        <form @submit.prevent="saveRealisasi" class="space-y-3.5">
          <div>
            <label class="form-label text-xs">Nilai Realisasi</label>
            <input
              v-model.number="realisasiForm.nilai_realisasi"
              type="number"
              step="0.01"
              required
              class="form-input text-xs"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="form-label text-xs">Keterangan / Kendala (Opsional)</label>
            <textarea
              v-model="realisasiForm.keterangan"
              rows="3"
              class="form-input text-xs resize-none"
              placeholder="Catatan pendukung capaian indikator..."
            />
          </div>

          <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" @click="realisasiModalOpen = false" class="btn-secondary text-xs">Batal</button>
            <button type="submit" class="btn-primary text-xs" :disabled="modalSubmitting">
              {{ modalSubmitting ? 'Menyimpan...' : 'Simpan Realisasi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>