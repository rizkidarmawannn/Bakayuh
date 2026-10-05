<script setup lang="ts">
import { ref, onMounted, computed, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { EvaluasiSakip, SatuanKerja, TahunAnggaran, StatusColor } from '@/types'
import StatusBadge from '@/components/common/StatusBadge.vue'

interface TrenItem {
  tahun: number
  satker: string
  nilai_total: number
  predikat: string
}

const route = useRoute()
const router = useRouter()
const { isSuperAdmin, isAdminKanwil } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const satkerId = Number(route.params.satkerId)
const loading = ref(true)

const satker = ref<SatuanKerja | null>(null)
const evaluasis = ref<EvaluasiSakip[]>([])
const tren = ref<TrenItem[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const activeTahun = ref<TahunAnggaran | null>(null)

// Current year evaluation
const currentEvaluasi = computed(() => {
  if (!activeTahun.value) return evaluasis.value[0] ?? null
  return evaluasis.value.find((e) => e.tahun_anggaran_id === activeTahun.value?.id) ?? null
})

// Modal Evaluation State
const evalModalOpen = ref(false)
const modalSubmitting = ref(false)

const evalForm = reactive({
  tahun_anggaran_id: 0,
  satker_id: satkerId,
  nilai_perencanaan: 80,
  nilai_pengukuran: 80,
  nilai_pelaporan: 80,
  nilai_evaluasi: 80,
  catatan: '',
})

const liveTotal = computed(() => {
  const p = Number(evalForm.nilai_perencanaan) || 0
  const peng = Number(evalForm.nilai_pengukuran) || 0
  const pel = Number(evalForm.nilai_pelaporan) || 0
  const e = Number(evalForm.nilai_evaluasi) || 0
  return Number(((p * 0.30) + (peng * 0.30) + (pel * 0.15) + (e * 0.25)).toFixed(2))
})

function getPredikatBadge(predikat: string | null | undefined): { label: string; color: StatusColor } {
  if (!predikat) return { label: 'Belum Ada', color: 'gray' }
  switch (predikat) {
    case 'AA':
    case 'A':
      return { label: predikat, color: 'green' }
    case 'BB':
    case 'B':
      return { label: predikat, color: 'blue' }
    case 'CC':
      return { label: predikat, color: 'yellow' }
    case 'C':
    case 'D':
      return { label: predikat, color: 'red' }
    default:
      return { label: predikat, color: 'gray' }
  }
}

async function loadData() {
  loading.value = true
  try {
    const [resSatker, resEval, resTren, resTahun] = await Promise.all([
      apiClient.get<{ data: SatuanKerja[] }>('/satker'),
      apiClient.get<{ data: EvaluasiSakip[] }>('/evaluasi-sakip', { params: { satker_id: satkerId } }),
      apiClient.get<{ data: TrenItem[] }>('/evaluasi-sakip/tren', { params: { satker_id: satkerId } }),
      apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran'),
    ])

    const found = resSatker.data.data.find((s) => s.id === satkerId)
    if (!found) {
      toastError('Error', 'Satuan kerja tidak ditemukan.')
      router.push('/sakip')
      return
    }
    satker.value = found
    evaluasis.value = resEval.data.data
    tren.value = resTren.data.data
    tahuns.value = resTahun.data.data
    activeTahun.value = tahuns.value.find((t) => t.is_aktif) || tahuns.value[0] || null
  } catch (err: any) {
    toastError('Gagal memuat data SAKIP', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openEditModal() {
  evalForm.tahun_anggaran_id = activeTahun.value?.id || 0
  evalForm.satker_id = satkerId
  if (currentEvaluasi.value) {
    evalForm.nilai_perencanaan = currentEvaluasi.value.nilai_perencanaan
    evalForm.nilai_pengukuran = currentEvaluasi.value.nilai_pengukuran
    evalForm.nilai_pelaporan = currentEvaluasi.value.nilai_pelaporan
    evalForm.nilai_evaluasi = currentEvaluasi.value.nilai_evaluasi
    evalForm.catatan = currentEvaluasi.value.catatan || ''
  }
  evalModalOpen.value = true
}

async function handleSaveEvaluasi() {
  modalSubmitting.value = true
  try {
    if (currentEvaluasi.value) {
      await apiClient.put(`/evaluasi-sakip/${currentEvaluasi.value.id}`, {
        nilai_perencanaan: evalForm.nilai_perencanaan,
        nilai_pengukuran: evalForm.nilai_pengukuran,
        nilai_pelaporan: evalForm.nilai_pelaporan,
        nilai_evaluasi: evalForm.nilai_evaluasi,
        catatan: evalForm.catatan,
      })
    } else {
      await apiClient.post('/evaluasi-sakip', evalForm)
    }
    toastSuccess('Berhasil', 'Evaluasi SAKIP berhasil disimpan.')
    evalModalOpen.value = false
    await loadData()
  } catch (err: any) {
    toastError('Gagal menyimpan evaluasi', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in max-w-5xl mx-auto pb-12">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
      <router-link
        to="/sakip"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-kemenkum-navy transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Komparasi SAKIP
      </router-link>

      <button
        v-if="(isSuperAdmin || isAdminKanwil) && satker"
        class="btn-primary text-xs inline-flex items-center gap-1.5"
        @click="openEditModal"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <span>{{ currentEvaluasi ? 'Ubah Nilai Evaluasi' : 'Input Nilai Evaluasi' }}</span>
      </button>
    </div>

    <div v-if="loading" class="card p-12 text-center text-slate-400">
      <svg class="w-8 h-8 mx-auto animate-spin text-kemenkum-navy mb-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
      </svg>
      <p class="text-xs font-bold">Memuat evaluasi SAKIP satker...</p>
    </div>

    <template v-else-if="satker">
      <!-- Satker Profile & Current SAKIP Overview Banner -->
      <div class="card p-6 bg-gradient-to-r from-kemenkum-navy to-slate-900 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gold text-slate-950">
                {{ satker.tipe }}
              </span>
              <span class="text-xs text-slate-300 font-mono">{{ satker.kode }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black mt-1 tracking-tight">{{ satker.nama }}</h1>
            <p class="text-xs text-slate-300 mt-1">
              Evaluasi SAKIP Tahun Anggaran {{ activeTahun?.tahun ?? '-' }}
            </p>
          </div>

          <!-- Total Score & Predikat Callout -->
          <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/15">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Nilai Akhir SAKIP</p>
              <p class="text-3xl font-black text-white mt-0.5">
                {{ currentEvaluasi?.nilai_total ?? '-' }}
                <span class="text-xs font-normal text-slate-300">/ 100</span>
              </p>
            </div>
            <div class="h-10 w-px bg-white/20"></div>
            <div class="text-center">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-300 mb-1">Predikat</p>
              <span
                class="inline-block px-3 py-1 rounded-xl text-base font-black tracking-wider shadow-sm"
                :class="{
                  'bg-emerald-400 text-slate-950': currentEvaluasi?.predikat === 'AA' || currentEvaluasi?.predikat === 'A',
                  'bg-blue-400 text-slate-950': currentEvaluasi?.predikat === 'BB' || currentEvaluasi?.predikat === 'B',
                  'bg-amber-400 text-slate-950': currentEvaluasi?.predikat === 'CC',
                  'bg-rose-400 text-white': currentEvaluasi?.predikat === 'C' || currentEvaluasi?.predikat === 'D',
                  'bg-slate-700 text-slate-300': !currentEvaluasi?.predikat,
                }"
              >
                {{ currentEvaluasi?.predikat ?? 'N/A' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 4 Components Breakdown Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Perencanaan Kinerja (30%) -->
        <div class="card p-5 bg-white border border-slate-200 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">1. Perencanaan</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 font-bold text-slate-600">Bobot 30%</span>
          </div>
          <div>
            <p class="text-2xl font-black text-slate-800">
              {{ currentEvaluasi?.nilai_perencanaan ?? '-' }}
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Kontribusi:
              <span class="font-bold text-kemenkum-navy">
                {{ currentEvaluasi ? ((currentEvaluasi.nilai_perencanaan * 0.3).toFixed(2)) : '-' }} poin
              </span>
            </p>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
            <div
              class="h-2 rounded-full bg-kemenkum-navy transition-all duration-500"
              :style="{ width: `${currentEvaluasi?.nilai_perencanaan ?? 0}%` }"
            ></div>
          </div>
        </div>

        <!-- 2. Pengukuran Kinerja (30%) -->
        <div class="card p-5 bg-white border border-slate-200 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">2. Pengukuran</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 font-bold text-slate-600">Bobot 30%</span>
          </div>
          <div>
            <p class="text-2xl font-black text-slate-800">
              {{ currentEvaluasi?.nilai_pengukuran ?? '-' }}
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Kontribusi:
              <span class="font-bold text-kemenkum-navy">
                {{ currentEvaluasi ? ((currentEvaluasi.nilai_pengukuran * 0.3).toFixed(2)) : '-' }} poin
              </span>
            </p>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
            <div
              class="h-2 rounded-full bg-kemenkum-navy transition-all duration-500"
              :style="{ width: `${currentEvaluasi?.nilai_pengukuran ?? 0}%` }"
            ></div>
          </div>
        </div>

        <!-- 3. Pelaporan Kinerja (15%) -->
        <div class="card p-5 bg-white border border-slate-200 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">3. Pelaporan</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 font-bold text-slate-600">Bobot 15%</span>
          </div>
          <div>
            <p class="text-2xl font-black text-slate-800">
              {{ currentEvaluasi?.nilai_pelaporan ?? '-' }}
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Kontribusi:
              <span class="font-bold text-kemenkum-navy">
                {{ currentEvaluasi ? ((currentEvaluasi.nilai_pelaporan * 0.15).toFixed(2)) : '-' }} poin
              </span>
            </p>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
            <div
              class="h-2 rounded-full bg-kemenkum-navy transition-all duration-500"
              :style="{ width: `${currentEvaluasi?.nilai_pelaporan ?? 0}%` }"
            ></div>
          </div>
        </div>

        <!-- 4. Evaluasi Internal (25%) -->
        <div class="card p-5 bg-white border border-slate-200 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">4. Evaluasi Akuntabilitas</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 font-bold text-slate-600">Bobot 25%</span>
          </div>
          <div>
            <p class="text-2xl font-black text-slate-800">
              {{ currentEvaluasi?.nilai_evaluasi ?? '-' }}
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Kontribusi:
              <span class="font-bold text-kemenkum-navy">
                {{ currentEvaluasi ? ((currentEvaluasi.nilai_evaluasi * 0.25).toFixed(2)) : '-' }} poin
              </span>
            </p>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
            <div
              class="h-2 rounded-full bg-kemenkum-navy transition-all duration-500"
              :style="{ width: `${currentEvaluasi?.nilai_evaluasi ?? 0}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Catatan Rekomendasi Kanwil Card -->
      <div class="card p-6 bg-white border border-slate-200 space-y-2">
        <h2 class="text-sm font-bold text-slate-800">Catatan & Rekomendasi Tim Penilai SAKIP</h2>
        <div v-if="currentEvaluasi?.catatan" class="p-4 bg-slate-50 rounded-xl text-xs text-slate-700 leading-relaxed">
          {{ currentEvaluasi.catatan }}
        </div>
        <p v-else class="text-xs text-slate-400 italic">
          Belum ada catatan atau rekomendasi spesifik yang dicatat oleh tim penilai Kanwil.
        </p>
      </div>

      <!-- Historical Trend Table -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div>
          <h2 class="text-sm font-bold text-slate-800">Tren Capaian SAKIP Lintas Tahun</h2>
          <p class="text-xs text-slate-400">Riwayat perkembangan nilai dan predikat akuntabilitas kinerja satuan kerja</p>
        </div>

        <div v-if="tren.length === 0" class="text-center py-6 text-xs text-slate-400">
          Belum ada riwayat evaluasi terdahulu.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="table-custom">
            <thead>
              <tr>
                <th class="w-24 text-center">Tahun</th>
                <th>Satuan Kerja</th>
                <th class="text-center w-36">Nilai Total</th>
                <th class="text-center w-36">Predikat</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in tren" :key="t.tahun" class="hover:bg-slate-50">
                <td class="text-center font-bold text-xs text-slate-700">{{ t.tahun }}</td>
                <td class="text-xs text-slate-600">{{ t.satker }}</td>
                <td class="text-center text-xs font-black text-slate-800">{{ t.nilai_total }}</td>
                <td class="text-center">
                  <StatusBadge
                    :label="getPredikatBadge(t.predikat).label"
                    :color="getPredikatBadge(t.predikat).color"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Modal Form for Update -->
    <div
      v-if="evalModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 class="text-base font-bold text-slate-800">Evaluasi SAKIP Satker</h2>
            <p class="text-xs text-slate-400">{{ satker?.nama }}</p>
          </div>
          <button
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
            @click="evalModalOpen = false"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleSaveEvaluasi">
          <!-- Component Scores Inputs -->
          <div class="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Skor 4 Komponen (0 - 100)</p>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">1. Perencanaan (30%)</label>
                <input
                  v-model.number="evalForm.nilai_perencanaan"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">2. Pengukuran (30%)</label>
                <input
                  v-model.number="evalForm.nilai_pengukuran"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">3. Pelaporan (15%)</label>
                <input
                  v-model.number="evalForm.nilai_pelaporan"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">4. Evaluasi (25%)</label>
                <input
                  v-model.number="evalForm.nilai_evaluasi"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="card p-3 bg-kemenkum-navy text-white flex items-center justify-between text-xs">
            <div>
              <span class="text-slate-300 block text-[10px]">Kalkulasi Nilai Otomatis</span>
              <span class="text-xl font-black">{{ liveTotal }} / 100</span>
            </div>
          </div>

          <!-- Catatan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Evaluator</label>
            <textarea
              v-model="evalForm.catatan"
              rows="3"
              class="input-text text-xs py-2 resize-none"
              placeholder="Catatan hasil penilaian..."
            ></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              class="btn-secondary text-xs"
              @click="evalModalOpen = false"
            >
              Batal
            </button>
            <button
              type="submit"
              class="btn-primary text-xs"
              :disabled="modalSubmitting"
            >
              <span>{{ modalSubmitting ? 'Menyimpan...' : 'Simpan Evaluasi' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>