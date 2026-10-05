<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { apiClient } from '@/composables/useApi'
import SkeletonCard from '@/components/common/SkeletonCard.vue'

interface PublikSummary {
  tahun: number
  total_satker: number
  rata_rata_capaian_iku: number
  nilai_sakip_rerata: number
  status_zi: string
}

const loading = ref(true)
const summary = ref<PublikSummary | null>(null)
const error = ref<string | null>(null)

async function fetchPublikSummary() {
  loading.value = true
  try {
    const res = await apiClient.get<{ data: PublikSummary }>('/publik/summary')
    summary.value = res.data.data
  } catch (err: any) {
    error.value = 'Gagal memuat informasi publik.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPublikSummary()
})
</script>

<template>
  <div class="space-y-8">
    <!-- Hero Banner -->
    <div class="card p-8 sm:p-10 bg-gradient-to-r from-kemenkum-navy via-kemenkum-navy-light to-slate-900 text-white shadow-xl">
      <div class="max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-kemenkum-gold/20 border border-kemenkum-gold/30 rounded-full text-kemenkum-gold text-xs font-semibold uppercase tracking-wider mb-4">
          Transparansi Publik
        </div>
        <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white leading-tight">
          Akuntabilitas Kinerja &amp; Reformasi Birokrasi
        </h1>
        <p class="text-sm sm:text-base text-kemenkum-silver/90 mt-2 leading-relaxed">
          Portal transparansi Kantor Wilayah Kementerian Hukum Kalimantan Selatan untuk menyajikan capaian indikator kinerja utama, evaluasi SAKIP, dan progres pembangunan Zona Integritas (WBK/WBBM) seluruh satuan kerja se-Kalimantan Selatan.
        </p>
      </div>
    </div>

    <!-- Public Highlights Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <template v-if="loading">
        <SkeletonCard v-for="i in 4" :key="i" :lines="2" />
      </template>

      <template v-else-if="summary">
        <!-- 1. Total Satker -->
        <div class="card p-6 border-t-4 border-kemenkum-navy">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Satuan Kerja Binaan</p>
          <p class="text-3xl font-black text-kemenkum-navy mt-2">{{ summary.total_satker }}</p>
          <p class="text-xs text-slate-500 mt-2">Lapas, Rutan, Kanim, Bapas, Rupbasan se-Kalsel</p>
        </div>

        <!-- 2. Rata-rata IKU -->
        <div class="card p-6 border-t-4 border-green-600">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-rata Capaian IKU</p>
          <p class="text-3xl font-black text-green-600 mt-2">{{ summary.rata_rata_capaian_iku.toFixed(1) }}%</p>
          <p class="text-xs text-slate-500 mt-2">Kinerja organisasi tahun anggaran {{ summary.tahun }}</p>
        </div>

        <!-- 3. Nilai SAKIP Rerata -->
        <div class="card p-6 border-t-4 border-blue-600">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nilai SAKIP Rerata</p>
          <p class="text-3xl font-black text-blue-600 mt-2">{{ summary.nilai_sakip_rerata ? summary.nilai_sakip_rerata.toFixed(2) : 'Belum Ada' }}</p>
          <p class="text-xs text-slate-500 mt-2">Akuntabilitas kinerja instansi</p>
        </div>

        <!-- 4. Komitmen ZI -->
        <div class="card p-6 border-t-4 border-kemenkum-gold">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pembangunan ZI</p>
          <p class="text-base font-black text-kemenkum-gold mt-2">{{ summary.status_zi }}</p>
          <p class="text-xs text-slate-500 mt-2">Wilayah Bebas dari Korupsi</p>
        </div>
      </template>
    </div>

    <!-- Transparency Statement -->
    <div class="card p-6 bg-slate-50 border border-slate-200">
      <h3 class="text-sm font-bold text-slate-800">Komitmen Keterbukaan Informasi</h3>
      <p class="text-xs text-slate-600 mt-1 leading-relaxed">
        Selaras dengan amanat Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik dan PermenPAN-RB tentang Reformasi Birokrasi, Kanwil Kemenkum Kalsel menyajikan data kinerja yang transparan, terukur, dan akuntabel demi terciptanya pelayanan publik yang prima dan berintegritas.
      </p>
    </div>
  </div>
</template>