<script setup lang="ts">
import { ref } from 'vue'

const dadukFilterTw = ref('Semua triwulan')
const twOptions = [
  'Semua triwulan',
  'Triwulan I',
  'Triwulan II',
  'Triwulan III',
  'Triwulan IV',
]

const dadukTableRows = ref([
  { unit: 'Setjen', total: 0, belum: 0, menunggu: 0, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'AHU', total: 0, belum: 0, menunggu: 0, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'KI', total: 0, belum: 0, menunggu: 0, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'PP', total: 0, belum: 0, menunggu: 0, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'BPHN', total: 0, belum: 0, menunggu: 0, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'BSK', total: 0, belum: 0, menunggu: 0, perbaikan: 0, diterima: 0, progres: 0 },
])
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in">
    <!-- Header with Filters & Action Buttons (Image 3) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Dashboard Monitoring Daduk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Status rekapitulasi data dukung seluruh unit kerja per triwulan
        </p>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="dadukFilterTw"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="tw in twOptions" :key="tw" :value="tw">{{ tw }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <button
          type="button"
          class="btn-secondary text-xs py-2 px-3.5 cursor-pointer shadow-xs"
        >
          Atur Tenggat
        </button>

        <button
          type="button"
          class="btn-secondary text-xs py-2 px-3.5 cursor-pointer shadow-xs"
        >
          Ekspor Rekap (CSV)
        </button>
      </div>
    </div>

    <!-- Monitoring Table Container (Image 3) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <!-- 5 Metric Boxes in a clean single row -->
      <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-y sm:divide-y-0 divide-slate-100 border-b border-slate-100 bg-slate-50/50">
        <div class="p-4 sm:p-5 text-center">
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F]">0</span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Total renaksi</span>
        </div>
        <div class="p-4 sm:p-5 text-center">
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F]">0%</span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Sudah diterima</span>
        </div>
        <div class="p-4 sm:p-5 text-center">
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F]">0</span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Menunggu verifikasi &gt;</span>
        </div>
        <div class="p-4 sm:p-5 text-center">
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F]">0</span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Perlu perbaikan</span>
        </div>
        <div class="p-4 sm:p-5 text-center col-span-2 sm:col-span-1">
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F]">0</span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Belum ada daduk</span>
        </div>
      </div>

      <!-- Flat Table -->
      <div class="table-container overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3 px-4 min-w-[160px] border-r border-white/10">UNIT</th>
              <th class="py-3 px-3 text-center w-20 border-r border-white/10">TOTAL</th>
              <th class="py-3 px-3 text-center w-20 border-r border-white/10">BELUM</th>
              <th class="py-3 px-3 text-center w-24 border-r border-white/10">MENUNGGU</th>
              <th class="py-3 px-3 text-center w-24 border-r border-white/10">PERBAIKAN</th>
              <th class="py-3 px-3 text-center w-24 border-r border-white/10">DITERIMA</th>
              <th class="py-3 px-4 min-w-[160px]">PROGRES</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="row in dadukTableRows"
              :key="row.unit"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="py-3.5 px-4 font-bold text-slate-900 text-xs sm:text-sm">
                {{ row.unit }}
              </td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.total }}</td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.belum }}</td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.menunggu }}</td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.perbaikan }}</td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.diterima }}</td>
              <td class="py-3.5 px-4">
                <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :class="row.progres > 0 ? 'bg-emerald-500' : 'bg-slate-200'"
                    :style="{ width: row.progres + '%' }"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="p-3.5 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-400">
        Klik baris unit untuk melihat sasaran dan indikatornya.
      </div>
    </div>
  </div>
</template>
