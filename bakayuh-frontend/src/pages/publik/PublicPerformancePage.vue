<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuthStore } from '@/stores/auth'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const router = useRouter()
const authStore = useAuthStore()

interface PublikSummary {
  tahun: number
  total_satker: number
  rata_rata_capaian_iku: number
  nilai_sakip_rerata: number
  status_zi: string
}

interface IkuItem {
  id: number
  kode: string
  nama: string
  satuan: string
  level: string
}

interface SakipLeaderboardItem {
  satker_nama: string
  satker_kode: string
  nilai_total: number
  predikat: string
}

const loading = ref(true)
const summary = ref<PublikSummary | null>(null)
const ikuList = ref<IkuItem[]>([])
const leaderboard = ref<SakipLeaderboardItem[]>([])
const activePillar = ref<'pohon' | 'cascading' | 'iku' | 'renaksi' | 'sakip' | 'spip'>('iku')

async function loadPublicData() {
  loading.value = true
  try {
    const [resSummary, resIku, resCharts] = await Promise.all([
      apiClient.get<{ data: PublikSummary }>('/publik/summary').catch(() => ({ data: { data: null } })),
      apiClient.get<{ data: IkuItem[] }>('/indikator-kinerja').catch(() => ({ data: { data: [] } })),
      apiClient.get<{ data: { sakip_leaderboard: SakipLeaderboardItem[] } }>('/dashboard/charts').catch(() => ({ data: { data: { sakip_leaderboard: [] } } })),
    ])

    if (resSummary.data.data) {
      summary.value = resSummary.data.data
    }
    if (resIku.data.data) {
      ikuList.value = resIku.data.data
    }
    if (resCharts.data.data?.sakip_leaderboard) {
      leaderboard.value = resCharts.data.data.sakip_leaderboard
    }
  } catch (err) {
    console.error('Error loading public performance data:', err)
  } finally {
    loading.value = false
  }
}

function scrollToSection(id: string) {
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' })
  }
}

onMounted(() => {
  loadPublicData()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-kemenkum-gold selection:text-slate-900">
    <!-- Top Navigation Bar (Menyerupai performance.kemenkumham.go.id) -->
    <header class="sticky top-0 z-50 bg-[#081B3F]/95 backdrop-blur border-b border-white/10 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo & Institution Name -->
        <div class="flex items-center gap-3.5 select-none">
          <LogoPengayoman :size="46" :show-text="false" class="flex-shrink-0" />
          <div class="flex flex-col">
            <span class="text-white font-black text-sm sm:text-base tracking-wider leading-tight">
              KEMENTERIAN HUKUM
            </span>
            <span class="text-kemenkum-gold text-[11px] sm:text-xs font-bold tracking-wide leading-tight">
              KANTOR WILAYAH KALIMANTAN SELATAN
            </span>
          </div>
        </div>

        <!-- Navigation Links -->
        <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-white hover:text-kemenkum-gold hover:bg-white/5 transition-colors"
            @click="scrollToSection('hero')"
          >
            Beranda
          </button>
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
            @click="activePillar = 'pohon'; scrollToSection('pilar')"
          >
            Pohon Kinerja
          </button>
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
            @click="activePillar = 'cascading'; scrollToSection('pilar')"
          >
            Cascading
          </button>
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
            @click="activePillar = 'iku'; scrollToSection('pilar')"
          >
            IKU
          </button>
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
            @click="activePillar = 'renaksi'; scrollToSection('pilar')"
          >
            Renaksi
          </button>
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
            @click="activePillar = 'sakip'; scrollToSection('pilar')"
          >
            SAKIP
          </button>
          <button
            type="button"
            class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
            @click="activePillar = 'spip'; scrollToSection('pilar')"
          >
            SPIP
          </button>
        </nav>

        <!-- Right Login Button -->
        <div class="flex items-center gap-3">
          <router-link
            v-if="authStore.isAuthenticated"
            to="/dashboard"
            class="px-4 py-2 rounded-xl bg-kemenkum-gold text-slate-950 font-bold text-xs hover:bg-kemenkum-gold-light transition-all flex items-center gap-2 shadow-md"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Buka Dashboard</span>
          </router-link>

          <router-link
            v-else
            to="/login"
            class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-all border border-white/20 flex items-center gap-2 shadow"
          >
            <svg class="w-4 h-4 text-kemenkum-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Login Sistem</span>
          </router-link>
        </div>
      </div>
    </header>

    <!-- Hero Section (Persis performance.kemenkumham.go.id) -->
    <section id="hero" class="relative bg-gradient-to-br from-[#081B3F] via-[#0C2B64] to-[#12397F] text-white overflow-hidden py-16 lg:py-24 border-b border-white/10">
      <!-- Background subtle patterns -->
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none" />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          <!-- Left Text Column -->
          <div class="lg:col-span-7 space-y-6">
            <!-- Pill Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-slate-900 font-extrabold text-xs tracking-wider shadow-md">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse" />
              <span>E-PERFORMANCE</span>
            </div>

            <!-- Big Main Headline -->
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
              Sistem Akuntabilitas Kinerja<br />
              <span class="text-kemenkum-gold">Kementerian Hukum RI</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed max-w-2xl font-normal">
              Aplikasi sistem akuntabilitas kinerja yang bertujuan untuk memudahkan proses pemantauan dan pengendalian kinerja dalam rangka meningkatkan akuntabilitas dan kinerja unit kerja di lingkungan Kantor Wilayah Kalimantan Selatan.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-wrap items-center gap-4 pt-2">
              <button
                type="button"
                class="px-6 py-3.5 rounded-xl bg-kemenkum-gold hover:bg-kemenkum-gold-light text-slate-950 font-extrabold text-sm transition-all shadow-lg flex items-center gap-2 group"
                @click="scrollToSection('pilar')"
              >
                <span>Eksplorasi Modul Kinerja</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </button>

              <router-link
                to="/login"
                class="px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-sm transition-all border border-white/20 flex items-center gap-2"
              >
                <span>Masuk Akun Pokja &bull; E-RB</span>
              </router-link>
            </div>

            <!-- Quick Metrics Strip -->
            <div class="grid grid-cols-3 gap-4 pt-6 border-t border-white/10 max-w-xl">
              <div>
                <span class="text-[11px] font-bold text-slate-300 block uppercase tracking-wider">Satuan Kerja</span>
                <span class="text-xl sm:text-2xl font-black text-white">17 Satker</span>
              </div>
              <div>
                <span class="text-[11px] font-bold text-slate-300 block uppercase tracking-wider">Capaian IKU</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-400">97.4%</span>
              </div>
              <div>
                <span class="text-[11px] font-bold text-slate-300 block uppercase tracking-wider">Predikat SAKIP</span>
                <span class="text-xl sm:text-2xl font-black text-kemenkum-gold">A (84.6)</span>
              </div>
            </div>
          </div>

          <!-- Right Visual / Building Illustration Column -->
          <div class="lg:col-span-5 flex justify-center">
            <div class="relative w-full max-w-md bg-gradient-to-tr from-slate-900 to-kemenkum-navy rounded-3xl p-3 shadow-2xl border border-white/15">
              <!-- Institutional Building Artcard -->
              <div class="relative rounded-2xl overflow-hidden bg-slate-950 aspect-[4/3] flex items-center justify-center border border-white/10">
                <!-- Decorative Graphic -->
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-slate-950/40 to-slate-950/90 z-10" />

                <svg viewBox="0 0 400 300" class="w-full h-full object-cover">
                  <!-- Sky / gradient -->
                  <defs>
                    <linearGradient id="skyGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                      <stop offset="0%" stop-color="#1E3A8A" />
                      <stop offset="100%" stop-color="#0B192C" />
                    </linearGradient>
                    <linearGradient id="glassGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#60A5FA" />
                      <stop offset="100%" stop-color="#1D4ED8" />
                    </linearGradient>
                  </defs>
                  <rect width="400" height="300" fill="url(#skyGrad)" />

                  <!-- Modern Government Building Architecture -->
                  <rect x="120" y="80" width="180" height="180" fill="#1E293B" stroke="#475569" stroke-width="2" rx="4" />
                  <!-- Glass Panels Grid -->
                  <g fill="url(#glassGrad)" opacity="0.85">
                    <rect x="135" y="100" width="22" height="30" rx="2" />
                    <rect x="165" y="100" width="22" height="30" rx="2" />
                    <rect x="195" y="100" width="22" height="30" rx="2" />
                    <rect x="225" y="100" width="22" height="30" rx="2" />
                    <rect x="255" y="100" width="22" height="30" rx="2" />

                    <rect x="135" y="145" width="22" height="30" rx="2" />
                    <rect x="165" y="145" width="22" height="30" rx="2" />
                    <rect x="195" y="145" width="22" height="30" rx="2" />
                    <rect x="225" y="145" width="22" height="30" rx="2" />
                    <rect x="255" y="145" width="22" height="30" rx="2" />

                    <rect x="135" y="190" width="22" height="30" rx="2" />
                    <rect x="165" y="190" width="22" height="30" rx="2" />
                    <rect x="195" y="190" width="22" height="30" rx="2" />
                    <rect x="225" y="190" width="22" height="30" rx="2" />
                    <rect x="255" y="190" width="22" height="30" rx="2" />
                  </g>

                  <!-- Left Tower Wing -->
                  <rect x="60" y="130" width="60" height="130" fill="#0F172A" stroke="#334155" stroke-width="2" rx="3" />
                  <g fill="#93C5FD" opacity="0.6">
                    <rect x="70" y="145" width="16" height="24" rx="2" />
                    <rect x="94" y="145" width="16" height="24" rx="2" />
                    <rect x="70" y="180" width="16" height="24" rx="2" />
                    <rect x="94" y="180" width="16" height="24" rx="2" />
                  </g>

                  <!-- Center Grand Entrance Columns -->
                  <rect x="180" y="225" width="60" height="35" fill="#C8993D" rx="2" />
                  <rect x="192" y="235" width="36" height="25" fill="#020617" />

                  <!-- Flagpole -->
                  <line x1="210" y1="35" x2="210" y2="80" stroke="#F1F5F9" stroke-width="3" />
                  <!-- Indonesian Flag -->
                  <rect x="210" y="38" width="30" height="10" fill="#EF4444" />
                  <rect x="210" y="48" width="30" height="10" fill="#FFFFFF" />
                </svg>

                <!-- Overlay Emblem Badge -->
                <div class="absolute bottom-4 left-4 right-4 z-20 flex items-center justify-between bg-slate-900/90 backdrop-blur border border-white/10 rounded-xl px-4 py-2.5">
                  <div class="flex items-center gap-2.5">
                    <LogoPengayoman :size="28" :show-text="false" />
                    <div>
                      <p class="text-xs font-bold text-white leading-tight">Kanwil Kemenkum Kalsel</p>
                      <p class="text-[10px] text-slate-400">Integrated Performance Hub</p>
                    </div>
                  </div>
                  <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section: 5 Pilar Informasi Kinerja Kementerian Hukum -->
    <section id="pilar" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      <!-- Section Header -->
      <div class="text-center max-w-2xl mx-auto space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-800 font-extrabold text-[11px] uppercase tracking-wider border border-amber-200">
          MODUL AKUNTABILITAS
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          5 PILAR INFORMASI KINERJA KEMENTERIAN HUKUM
        </h2>
        <p class="text-xs sm:text-sm text-slate-500">
          Siklus tata kelola akuntabilitas kinerja terintegrasi dari tahap perencanaan hingga evaluasi instansi
        </p>
      </div>

      <!-- 5 Pillars Navigation Cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- 1. Pohon Kinerja -->
        <button
          type="button"
          class="p-4 rounded-2xl border text-left transition-all duration-200 flex flex-col justify-between"
          :class="activePillar === 'pohon'
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-lg scale-[1.02]'
            : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
          @click="activePillar = 'pohon'"
        >
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm mb-3"
            :class="activePillar === 'pohon' ? 'bg-gold text-slate-950' : 'bg-slate-100 text-slate-700'">
            01
          </div>
          <div>
            <h3 class="text-sm font-black leading-snug">Pohon Kinerja</h3>
            <p class="text-[11px] mt-1 leading-normal" :class="activePillar === 'pohon' ? 'text-slate-200' : 'text-slate-400'">
              Struktur cascading sasaran strategis, program, dan kegiatan.
            </p>
          </div>
        </button>

        <!-- 2. Cascading Kinerja -->
        <button
          type="button"
          class="p-4 rounded-2xl border text-left transition-all duration-200 flex flex-col justify-between"
          :class="activePillar === 'cascading'
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-lg scale-[1.02]'
            : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
          @click="activePillar = 'cascading'"
        >
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm mb-3"
            :class="activePillar === 'cascading' ? 'bg-gold text-slate-950' : 'bg-slate-100 text-slate-700'">
            02
          </div>
          <div>
            <h3 class="text-sm font-black leading-snug">Cascading Kinerja</h3>
            <p class="text-[11px] mt-1 leading-normal" :class="activePillar === 'cascading' ? 'text-slate-200' : 'text-slate-400'">
              Penurunan target kinerja eselon hingga pelaksana 17 satker.
            </p>
          </div>
        </button>

        <!-- 3. Indikator Kinerja Utama (IKU) -->
        <button
          type="button"
          class="p-4 rounded-2xl border text-left transition-all duration-200 flex flex-col justify-between"
          :class="activePillar === 'iku'
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-lg scale-[1.02]'
            : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
          @click="activePillar = 'iku'"
        >
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm mb-3"
            :class="activePillar === 'iku' ? 'bg-gold text-slate-950' : 'bg-slate-100 text-slate-700'">
            03
          </div>
          <div>
            <h3 class="text-sm font-black leading-snug">Indikator IKU</h3>
            <p class="text-[11px] mt-1 leading-normal" :class="activePillar === 'iku' ? 'text-slate-200' : 'text-slate-400'">
              Pengukuran capaian target perjanjian kinerja berkala.
            </p>
          </div>
        </button>

        <!-- 4. Rencana Aksi (Renaksi) -->
        <button
          type="button"
          class="p-4 rounded-2xl border text-left transition-all duration-200 flex flex-col justify-between"
          :class="activePillar === 'renaksi'
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-lg scale-[1.02]'
            : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
          @click="activePillar = 'renaksi'"
        >
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm mb-3"
            :class="activePillar === 'renaksi' ? 'bg-gold text-slate-950' : 'bg-slate-100 text-slate-700'">
            04
          </div>
          <div>
            <h3 class="text-sm font-black leading-snug">Rencana Aksi</h3>
            <p class="text-[11px] mt-1 leading-normal" :class="activePillar === 'renaksi' ? 'text-slate-200' : 'text-slate-400'">
              Tahapan aksi per triwulan (TW I - IV) dan bukti dukung.
            </p>
          </div>
        </button>

        <!-- 5. Evaluasi SAKIP -->
        <button
          type="button"
          class="p-4 rounded-2xl border text-left transition-all duration-200 flex flex-col justify-between col-span-2 md:col-span-1"
          :class="activePillar === 'sakip'
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-lg scale-[1.02]'
            : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
          @click="activePillar = 'sakip'"
        >
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm mb-3"
            :class="activePillar === 'sakip' ? 'bg-gold text-slate-950' : 'bg-slate-100 text-slate-700'">
            05
          </div>
          <div>
            <h3 class="text-sm font-black leading-snug">Evaluasi SAKIP</h3>
            <p class="text-[11px] mt-1 leading-normal" :class="activePillar === 'sakip' ? 'text-slate-200' : 'text-slate-400'">
              4 Komponen akuntabilitas (Perencanaan, Pengukuran, Pelaporan, Evaluasi).
            </p>
          </div>
        </button>
      </div>

      <!-- Pillar Active Detail Box -->
      <div class="card p-6 sm:p-8 bg-white border border-slate-200 shadow-sm space-y-6">
        <!-- 1. Pohon Kinerja Content -->
        <div v-if="activePillar === 'pohon'" class="space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <div>
              <h3 class="text-lg font-black text-slate-900">Arsitektur Pohon Kinerja (Logic Model)</h3>
              <p class="text-xs text-slate-500">Penyelarasan mandat perundang-undangan dan sasaran strategis Kanwil Kemenkumham Kalsel</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">Modul Perencanaan</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
              <span class="text-[10px] font-bold uppercase text-slate-400">Tingkat 1 - Ultimate Outcome</span>
              <h4 class="text-sm font-bold text-slate-800 mt-1">Kepastian Hukum &amp; Pelayanan Berkualitas</h4>
              <p class="text-xs text-slate-500 mt-1">Terwujudnya supremasi hukum yang berkeadilan dan pelayanan hukum yang inklusif di wilayah Kalimantan Selatan.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
              <span class="text-[10px] font-bold uppercase text-slate-400">Tingkat 2 - Intermediate Outcome</span>
              <h4 class="text-sm font-bold text-slate-800 mt-1">Akuntabilitas &amp; Reformasi Satker</h4>
              <p class="text-xs text-slate-500 mt-1">Optimalisasi tata kelola birokrasi berintegritas tinggi bebas korupsi (WBK/WBBM) di 17 UPT Pemasyarakatan dan Imigrasi.</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
              <span class="text-[10px] font-bold uppercase text-slate-400">Tingkat 3 - Output / Program</span>
              <h4 class="text-sm font-bold text-slate-800 mt-1">Aksi Triwulan Terukur</h4>
              <p class="text-xs text-slate-500 mt-1">Pelaksanaan rencana aksi berkala TW I hingga TW IV dengan kepatuhan upload data dukung 100%.</p>
            </div>
          </div>
        </div>

        <!-- 2. Cascading Content -->
        <div v-else-if="activePillar === 'cascading'" class="space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <div>
              <h3 class="text-lg font-black text-slate-900">Cascading Kinerja Lintas Satuan Kerja</h3>
              <p class="text-xs text-slate-500">Penyelarasan target kinerja dari Kepala Kantor Wilayah ke 17 Satuan Kerja</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">17 Satker Terhubung</span>
          </div>

          <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-xl flex items-center justify-between">
            <div class="space-y-1">
              <h4 class="text-xs font-bold text-emerald-900">Perjanjian Kinerja Terpadu</h4>
              <p class="text-xs text-emerald-700">Setiap target indikator kinerja utama (IKU) secara otomatis diturunkan menjadi target tahunan pada satuan kerja terkait.</p>
            </div>
            <router-link to="/login" class="btn-primary py-2 px-3 text-xs bg-emerald-700 text-white font-bold whitespace-nowrap">
              Lihat Cascading Lengkap
            </router-link>
          </div>
        </div>

        <!-- 3. IKU Content -->
        <div v-else-if="activePillar === 'iku'" class="space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <div>
              <h3 class="text-lg font-black text-slate-900">Daftar Indikator Kinerja Utama (IKU) Resmi</h3>
              <p class="text-xs text-slate-500">7 Indikator Kinerja Utama tingkat strategis dan program Kanwil Kemenkumham Kalsel</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">Tahun Anggaran 2026</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-600 font-bold border-b">
                <tr>
                  <th class="py-2.5 px-3">Kode IKU</th>
                  <th class="py-2.5 px-3">Nama Indikator</th>
                  <th class="py-2.5 px-3">Satuan</th>
                  <th class="py-2.5 px-3">Tingkat</th>
                  <th class="py-2.5 px-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="iku in ikuList" :key="iku.id" class="hover:bg-slate-50">
                  <td class="py-2.5 px-3 font-mono font-bold text-kemenkum-navy">{{ iku.kode }}</td>
                  <td class="py-2.5 px-3 font-semibold text-slate-800">{{ iku.nama }}</td>
                  <td class="py-2.5 px-3 text-slate-500">{{ iku.satuan }}</td>
                  <td class="py-2.5 px-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                      :class="iku.level === 'strategis' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'">
                      {{ iku.level }}
                    </span>
                  </td>
                  <td class="py-2.5 px-3 text-right">
                    <router-link to="/login" class="text-kemenkum-navy font-bold hover:underline">
                      Detail Matriks &rarr;
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 4. Renaksi Content -->
        <div v-else-if="activePillar === 'renaksi'" class="space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <div>
              <h3 class="text-lg font-black text-slate-900">Rencana Aksi Kinerja Triwulanan (Renaksi)</h3>
              <p class="text-xs text-slate-500">Pemantauan progres pelaksanaan tahapan aksi dan validasi bukti dukung</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold">Triwulan I - IV</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-1">
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-center">
              <span class="text-xs font-bold text-slate-500">Triwulan I</span>
              <p class="text-lg font-black text-slate-800 mt-0.5">Jan - Mar</p>
              <span class="inline-block mt-2 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">100% Terlapor</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-center">
              <span class="text-xs font-bold text-slate-500">Triwulan II</span>
              <p class="text-lg font-black text-slate-800 mt-0.5">Apr - Jun</p>
              <span class="inline-block mt-2 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">100% Terlapor</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-center">
              <span class="text-xs font-bold text-slate-500">Triwulan III</span>
              <p class="text-lg font-black text-slate-800 mt-0.5">Jul - Sep</p>
              <span class="inline-block mt-2 px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold">Berjalan</span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-center">
              <span class="text-xs font-bold text-slate-500">Triwulan IV</span>
              <p class="text-lg font-black text-slate-800 mt-0.5">Okt - Des</p>
              <span class="inline-block mt-2 px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-bold">Persiapan</span>
            </div>
          </div>
        </div>

        <!-- 5. SAKIP Content -->
        <div v-else-if="activePillar === 'sakip'" class="space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <div>
              <h3 class="text-lg font-black text-slate-900">Peringkat Evaluasi SAKIP Satuan Kerja</h3>
              <p class="text-xs text-slate-500">Komparasi nilai SAKIP 4 komponen utama satker jajaran Kanwil Kalsel</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold">Hasil Evaluasi 2026</span>
          </div>

          <div v-if="leaderboard.length" class="overflow-x-auto">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 text-slate-600 font-bold border-b">
                <tr>
                  <th class="py-2.5 px-3">Peringkat</th>
                  <th class="py-2.5 px-3">Satuan Kerja</th>
                  <th class="py-2.5 px-3">Kode Satker</th>
                  <th class="py-2.5 px-3 text-center">Nilai SAKIP</th>
                  <th class="py-2.5 px-3 text-center">Predikat</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(item, idx) in leaderboard.slice(0, 5)" :key="item.satker_kode" class="hover:bg-slate-50">
                  <td class="py-2.5 px-3 font-bold text-slate-700">#{{ idx + 1 }}</td>
                  <td class="py-2.5 px-3 font-bold text-slate-900">{{ item.satker_nama }}</td>
                  <td class="py-2.5 px-3 font-mono text-slate-500">{{ item.satker_kode }}</td>
                  <td class="py-2.5 px-3 text-center font-black text-kemenkum-navy">{{ item.nilai_total }}</td>
                  <td class="py-2.5 px-3 text-center">
                    <span class="px-2 py-0.5 rounded-full font-black text-[10px]"
                      :class="item.predikat === 'AA' || item.predikat === 'A' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'">
                      {{ item.predikat }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 6. SPIP Content -->
        <div v-else class="space-y-4">
          <div class="flex items-center justify-between border-b pb-3">
            <div>
              <h3 class="text-lg font-black text-slate-900">Sistem Pengendalian Intern Pemerintah (SPIP)</h3>
              <p class="text-xs text-slate-500">Maturitas SPIP terintegrasi dalam manajemen risiko dan pengendalian intern kinerja</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-bold">Tingkat Maturitas 3+</span>
          </div>
          <p class="text-xs text-slate-600 leading-relaxed">
            Pengendalian intern dan mitigasi risiko kinerja dilaksanakan secara berjenjang oleh Unit Pemilik Risiko (UPR) di setiap Satuan Kerja guna memastikan tercapainya target IKU dan pembangunan Zona Integritas secara akuntabel.
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#081B3F] text-slate-400 text-xs py-10 border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <LogoPengayoman :size="32" :show-text="false" />
          <div>
            <p class="text-white font-bold text-sm">BAKAYUH</p>
            <p class="text-[11px] text-slate-400">Kantor Wilayah Kementerian Hukum dan HAM Kalimantan Selatan</p>
          </div>
        </div>
        <p class="text-[11px] text-center sm:text-right">
          &copy; 2026 E-Performance &amp; E-RB Terpadu &bull; Kementerian Hukum Republik Indonesia
        </p>
      </div>
    </footer>
  </div>
</template>
