<script setup lang="ts">
import { ref, onMounted } from 'vue'

export interface JenisLaporanItem {
  id: string
  nama: string
  periode: 'Bulanan' | 'Triwulan' | 'Semester' | 'Tahunan'
  tenggat: string
  unitWajib: string
  status: 'Aktif' | 'Nonaktif'
  keterangan?: string
}

const defaultItems: JenisLaporanItem[] = [
  {
    id: 'jl-1',
    nama: 'Laporan Bulanan',
    periode: 'Bulanan',
    tenggat: 'Tanggal 10 bulan berikutnya',
    unitWajib: 'Semua unit',
    status: 'Aktif',
    keterangan: 'Laporan realisasi kegiatan dan anggaran bulanan seluruh unit kerja',
  },
  {
    id: 'jl-2',
    nama: 'Laporan Triwulan',
    periode: 'Triwulan',
    tenggat: 'Tanggal 10 bulan berikutnya',
    unitWajib: 'Semua unit',
    status: 'Aktif',
    keterangan: 'Laporan capaian kinerja dan evaluasi triwulanan',
  },
]

const items = ref<JenisLaporanItem[]>([...defaultItems])

const STORAGE_KEY = 'bakayuh_jenis_laporan_data'

function loadData() {
  if (typeof window !== 'undefined') {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) {
      try {
        const parsed = JSON.parse(raw)
        if (Array.isArray(parsed) && parsed.length > 0) {
          items.value = parsed
          return
        }
      } catch (e) {
        console.warn('Failed parsing jenis laporan data', e)
      }
    }
  }
}

function saveData() {
  if (typeof window !== 'undefined') {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
  }
}

onMounted(() => {
  loadData()
})

// Toast
const toastMessage = ref('')
const showToast = ref(false)
function triggerToast(msg: string) {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2500)
}

// Modal State
const showModal = ref(false)
const modalMode = ref<'add' | 'edit'>('add')
const editId = ref('')

const formModel = ref<{
  nama: string
  periode: 'Bulanan' | 'Triwulan' | 'Semester' | 'Tahunan'
  tenggat: string
  unitWajib: string
  status: 'Aktif' | 'Nonaktif'
  keterangan: string
}>({
  nama: '',
  periode: 'Bulanan',
  tenggat: 'Tanggal 10 bulan berikutnya',
  unitWajib: 'Semua unit',
  status: 'Aktif',
  keterangan: '',
})

function openAddModal() {
  modalMode.value = 'add'
  editId.value = ''
  formModel.value = {
    nama: '',
    periode: 'Bulanan',
    tenggat: 'Tanggal 10 bulan berikutnya',
    unitWajib: 'Semua unit',
    status: 'Aktif',
    keterangan: '',
  }
  showModal.value = true
}

function openEditModal(item: JenisLaporanItem) {
  modalMode.value = 'edit'
  editId.value = item.id
  formModel.value = {
    nama: item.nama,
    periode: item.periode,
    tenggat: item.tenggat,
    unitWajib: item.unitWajib,
    status: item.status,
    keterangan: item.keterangan || '',
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function handleSave() {
  if (!formModel.value.nama.trim()) {
    alert('Nama laporan wajib diisi')
    return
  }

  if (modalMode.value === 'add') {
    items.value.push({
      id: 'jl-' + Date.now(),
      nama: formModel.value.nama.trim(),
      periode: formModel.value.periode,
      tenggat: formModel.value.tenggat.trim() || 'Tanggal 10 bulan berikutnya',
      unitWajib: formModel.value.unitWajib.trim() || 'Semua unit',
      status: formModel.value.status,
      keterangan: formModel.value.keterangan.trim(),
    })
    triggerToast('Jenis laporan baru berhasil ditambahkan!')
  } else {
    const idx = items.value.findIndex((i) => i.id === editId.value)
    const existing = items.value[idx]
    if (idx !== -1 && existing) {
      items.value[idx] = {
        id: existing.id,
        nama: formModel.value.nama.trim(),
        periode: formModel.value.periode,
        tenggat: formModel.value.tenggat.trim() || 'Tanggal 10 bulan berikutnya',
        unitWajib: formModel.value.unitWajib.trim() || 'Semua unit',
        status: formModel.value.status,
        keterangan: formModel.value.keterangan.trim(),
      }
      triggerToast('Jenis laporan berhasil diperbarui!')
    }
  }

  saveData()
  closeModal()
}

function handleDelete(item: JenisLaporanItem) {
  if (confirm(`Yakin ingin menghapus jenis laporan "${item.nama}"?`)) {
    items.value = items.value.filter((i) => i.id !== item.id)
    saveData()
    triggerToast(`Jenis laporan "${item.nama}" berhasil dihapus.`)
  }
}
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in relative">
    <!-- Toast Feedback -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0B2A6F] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-white/20"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Header: Title, Subtitle & Action Button (Matching Reference Screenshot) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          Jenis Laporan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Atur laporan yang wajib dikumpulkan unit, tenggat, dan unit yang wajib.
        </p>
      </div>

      <button
        type="button"
        @click="openAddModal"
        class="self-start sm:self-auto px-4 py-2 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs sm:text-sm font-semibold shadow-xs transition-colors cursor-pointer inline-flex items-center gap-1.5"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Jenis laporan</span>
      </button>
    </div>

    <!-- Table Container (Matching Exact Layout & Headers) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3.5 px-5">NAMA</th>
              <th class="py-3.5 px-4">PERIODE</th>
              <th class="py-3.5 px-4">TENGGAT</th>
              <th class="py-3.5 px-4">UNIT WAJIB</th>
              <th class="py-3.5 px-4 text-center">STATUS</th>
              <th class="py-3.5 px-5 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="row in items"
              :key="row.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <!-- NAMA -->
              <td class="py-3.5 px-5 font-bold text-slate-900 text-sm">
                {{ row.nama }}
              </td>

              <!-- PERIODE -->
              <td class="py-3.5 px-4 text-slate-600 font-medium">
                {{ row.periode }}
              </td>

              <!-- TENGGAT -->
              <td class="py-3.5 px-4 text-slate-600 font-medium">
                {{ row.tenggat }}
              </td>

              <!-- UNIT WAJIB -->
              <td class="py-3.5 px-4 text-slate-600 font-medium">
                {{ row.unitWajib }}
              </td>

              <!-- STATUS -->
              <td class="py-3.5 px-4 text-center">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                  :class="row.status === 'Aktif'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-slate-100 text-slate-600 border-slate-200'"
                >
                  {{ row.status }}
                </span>
              </td>

              <!-- AKSI -->
              <td class="py-3.5 px-5 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5 justify-end">
                  <button
                    type="button"
                    @click="openEditModal(row)"
                    class="px-3 py-1 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors cursor-pointer"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    @click="handleDelete(row)"
                    class="px-3 py-1 rounded-md border border-rose-200 bg-white text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:border-rose-300 transition-colors cursor-pointer"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty Row State -->
            <tr v-if="items.length === 0">
              <td colspan="6" class="py-12 text-center text-slate-400">
                Belum ada jenis laporan. Klik tombol "+ Jenis laporan" untuk menambahkan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL DIALOG: TAMBAH / EDIT JENIS LAPORAN -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden animate-scale-up">
        <!-- Modal Header -->
        <div class="bg-[#0B2A6F] text-white p-5 flex items-center justify-between">
          <h3 class="text-base font-bold">
            {{ modalMode === 'add' ? 'Tambah Jenis Laporan' : 'Edit Jenis Laporan' }}
          </h3>
          <button
            type="button"
            @click="closeModal"
            class="text-slate-300 hover:text-white rounded-lg p-1 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Form Body -->
        <form @submit.prevent="handleSave" class="p-5 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Laporan</label>
            <input
              v-model="formModel.nama"
              type="text"
              required
              placeholder="Contoh: Laporan Bulanan"
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Periode</label>
              <select
                v-model="formModel.periode"
                class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              >
                <option value="Bulanan">Bulanan</option>
                <option value="Triwulan">Triwulan</option>
                <option value="Semester">Semester</option>
                <option value="Tahunan">Tahunan</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Status</label>
              <select
                v-model="formModel.status"
                class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              >
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Tenggat Waktu</label>
            <input
              v-model="formModel.tenggat"
              type="text"
              placeholder="Contoh: Tanggal 10 bulan berikutnya"
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Unit Wajib Mengumpulkan</label>
            <input
              v-model="formModel.unitWajib"
              type="text"
              placeholder="Contoh: Semua unit"
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
            <textarea
              v-model="formModel.keterangan"
              rows="2"
              placeholder="Deskripsi singkat mengenai laporan ini"
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            ></textarea>
          </div>

          <!-- Modal Footer -->
          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 rounded-lg border border-slate-200 bg-white font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold cursor-pointer"
            >
              Simpan Data
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
