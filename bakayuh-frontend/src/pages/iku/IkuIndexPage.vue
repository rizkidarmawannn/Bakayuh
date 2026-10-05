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

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedTargets,
} = usePagination(targets, { defaultPageSize: 10 })

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
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Indikator Kinerja Utama (IKU)</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Pengelolaan target tahunan dan pelaporan realisasi IKU Satuan Kerja
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <RouterLink to="/iku/matriks" class="btn-secondary text-xs py-2 px-3 font-semibold">
          Lihat Matriks &rarr;
        </RouterLink>
        <button
          v-if="isSuperAdmin || isAdminKanwil"
          type="button"
          @click="openTargetModal"
          class="btn-primary text-xs py-2 px-3 shadow"
        >
          + Tetapkan Target IKU
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4 flex flex-wrap items-center gap-4 text-xs">
      <div class="flex items-center gap-2">
        <label class="font-semibold text-slate-600">Tahun:</label>
        <select
          v-model="selectedTahunId"
          @change="fetchTargets"
          class="form-select text-xs py-1.5 px-3"
        >
          <option v-for="t in tahuns" :key="t.id" :value="t.id">
            {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
          </option>
        </select>
      </div>

      <div v-if="isSuperAdmin || isAdminKanwil" class="flex items-center gap-2">
        <label class="font-semibold text-slate-600">Satuan Kerja:</label>
        <select
          v-model="selectedSatkerId"
          @change="fetchTargets"
          class="form-select text-xs py-1.5 px-3 max-w-xs"
        >
          <option :value="null">-- Semua Satker --</option>
          <option v-for="s in satkers" :key="s.id" :value="s.id">
            {{ s.nama }}
          </option>
        </select>
      </div>
    </div>

    <!-- Data Table -->
    <SkeletonTable v-if="loading" :rows="6" :cols="6" />

    <div v-else-if="targets.length === 0" class="card p-12 text-center text-slate-400">
      <p class="font-semibold text-sm">Belum ada data Target IKU pada filter yang dipilih.</p>
      <p class="text-xs mt-1">Gunakan tombol "Tetapkan Target IKU" untuk menginput target.</p>
    </div>

    <div v-else class="table-container shadow-sm">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-12 text-center">No</th>
            <th>Indikator Kinerja</th>
            <th>Satuan Kerja</th>
            <th class="text-right">Target</th>
            <th class="text-right">Realisasi</th>
            <th class="text-center">Capaian</th>
            <th class="text-center w-28">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(t, idx) in paginatedTargets" :key="t.id">
            <td class="text-center text-xs text-slate-400">{{ (currentPage - 1) * pageSize + idx + 1 }}</td>
            <td>
              <div class="font-bold text-slate-800 text-xs">{{ t.indikator?.nama }}</div>
              <div class="text-[10px] text-slate-400 font-mono">{{ t.indikator?.kode }} &bull; Satuan: {{ t.indikator?.satuan }}</div>
            </td>
            <td>
              <div class="text-xs font-medium text-slate-700">{{ t.satker?.nama }}</div>
            </td>
            <td class="text-right font-mono font-semibold text-xs text-slate-800">
              {{ Number(t.nilai_target).toFixed(2) }}
            </td>
            <td class="text-right font-mono font-semibold text-xs">
              <span v-if="t.realisasi">{{ Number(t.realisasi.nilai_realisasi).toFixed(2) }}</span>
              <span v-else class="text-slate-300">&mdash;</span>
            </td>
            <td class="text-center">
              <div v-if="t.realisasi" class="inline-flex items-center gap-1.5">
                <span
                  class="badge-green text-xs font-bold"
                  :class="{
                    'badge-green': Number(t.realisasi.persentase_capaian) >= 100,
                    'badge-yellow': Number(t.realisasi.persentase_capaian) >= 80 && Number(t.realisasi.persentase_capaian) < 100,
                    'badge-red': Number(t.realisasi.persentase_capaian) < 80,
                  }"
                >
                  {{ Number(t.realisasi.persentase_capaian).toFixed(1) }}%
                </span>
              </div>
              <span v-else class="text-slate-300 text-xs">Belum lapor</span>
            </td>
            <td class="text-center">
              <button
                type="button"
                @click="openRealisasiModal(t)"
                class="btn-secondary text-[11px] py-1 px-2.5"
              >
                {{ t.realisasi ? 'Ubah Realisasi' : 'Input Realisasi' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <AppPagination
        v-if="targets.length > 0"
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