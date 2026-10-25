<script setup lang="ts">
import { ref } from 'vue'

const selectedUnit = ref('Semua unit')
const unitOptions = [
  'Semua unit',
  'Setjen',
  'AHU',
  'KI',
  'PP',
  'BPHN',
  'BSK',
]

const selectedTw = ref('Semua TW')
const twOptions = [
  'Semua TW',
  'TW 1',
  'TW 2',
  'TW 3',
  'TW 4',
]

const selectedStatus = ref('Menunggu')
const statusOptions = [
  'Menunggu',
  'Perlu Perbaikan',
  'Diterima',
  'Semua Status',
]
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in">
    <!-- Header with Filters (Image 5) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Verifikasi Daduk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Pemeriksaan dan validasi bukti data dukung rencana aksi per unit kerja
        </p>
      </div>

      <!-- Filter Dropdowns: 1 Baris Mendatar (Horizontal Row) dengan Panah Berjarak Nyaman -->
      <div class="flex items-center gap-2.5 flex-nowrap overflow-x-auto sm:overflow-visible">
        <!-- 1. Filter Unit -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedUnit"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- 2. Filter Triwulan -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedTw"
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

        <!-- 3. Filter Status -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedStatus"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Panel (Image 5) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-12 text-center">
      <div class="max-w-md mx-auto py-12 space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-sm font-semibold text-slate-600">
          Tidak ada daduk pada filter ini.
        </p>
        <p class="text-xs text-slate-400">
          Semua data dukung telah terverifikasi atau belum ada unggahan baru dari satuan kerja.
        </p>
      </div>
    </div>
  </div>
</template>
