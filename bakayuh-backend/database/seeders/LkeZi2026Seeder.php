<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RbArea;
use App\Models\RbIndikator;
use App\Models\RbSubIndikator;
use App\Enums\KategoriRb;
use App\Enums\KomponenRb;
use App\Enums\AspekRb;

class LkeZi2026Seeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            // ==========================================
            // KOMPONEN PENGUNGKIT (6 AREA + DOKUMEN TAMBAHAN)
            // ==========================================
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-A1',
                'nama_area' => 'I. Manajemen Perubahan',
                'urutan' => 1,
                'indikators' => [
                    [
                        'kode' => 'A1-IND-01',
                        'nama_indikator' => 'Penyusunan Tim Kerja Pembangunan ZI',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Pembentukan Tim Kerja melalui seleksi terbuka, SK Tim Kerja, dan pembagian tugas yang jelas.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Pembentukan Tim Kerja Pembangunan ZI secara transparan dan akuntabel',
                                'checklist_daduk' => "a. Undangan rapat pembentukan Tim Kerja ZI\nb. Notula rapat & daftar hadir\nc. SK Penetapan Tim Kerja ZI ditandatangani Kepala Satker\nd. Foto/dokumentasi proses seleksi dan pembentukan",
                                'catatan_tpi' => 'Pastikan SK memuat seluruh anggota 6 pokja dan tidak ada duplikasi fungsi.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'A1-IND-02',
                        'nama_indikator' => 'Rencana Pembangunan Zona Integritas',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Dokumen rencana kerja pembangunan ZI yang memuat target prioritas dan target waktu.',
                        'urutan' => 2,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Penyusunan Dokumen Rencana Kerja Pembangunan ZI',
                                'checklist_daduk' => "a. Dokumen Rencana Kerja ZI tahun berjalan\nb. Matriks kegiatan 6 pokja pengungkit\nc. Notula rapat pembahasan rencana kerja",
                                'catatan_tpi' => 'Rencana kerja harus selaras dengan target kinerja tahunan satker.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'A1-IND-03',
                        'nama_indikator' => 'Pola Pikir dan Budaya Kerja (Role Model & Agen Perubahan)',
                        'aspek' => AspekRb::Reform,
                        'keterangan_juknis' => 'Keteladanan pimpinan sebagai role model dan penetapan Agen Perubahan yang aktif membawa inovasi.',
                        'urutan' => 3,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Pimpinan sebagai Role Model dalam Pembangunan ZI',
                                'checklist_daduk' => "a. Dokumentasi pimpinan memimpin apel/briefing integritas\nb. Laporan keteladanan jam kerja pimpinan (absensi)\nc. Arahan tertulis pimpinan terkait pencegahan gratifikasi & pungli",
                                'catatan_tpi' => 'Tampilkan konsistensi keteladanan pimpinan sepanjang periode pelaporan.',
                            ],
                            [
                                'nomor_poin' => 2,
                                'judul_poin' => 'Penetapan dan Rencana Aksi Agen Perubahan (Agent of Change)',
                                'checklist_daduk' => "a. SK Penetapan Agen Perubahan\nb. Rencana aksi inovasi Agen Perubahan\nc. Laporan capaian hasil inovasi agen perubahan",
                                'catatan_tpi' => 'Inovasi agen perubahan harus memiliki dampak nyata pada efisiensi layanan atau integritas.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-A2',
                'nama_area' => 'II. Penataan Tatalaksana',
                'urutan' => 2,
                'indikators' => [
                    [
                        'kode' => 'A2-IND-01',
                        'nama_indikator' => 'Standar Operasional Prosedur (SOP) Kegiatan Utama',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Penerapan, evaluasi, dan pemutakhiran SOP kegiatan utama organisasi.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Penyusunan dan Evaluasi SOP Kegiatan Utama',
                                'checklist_daduk' => "a. Dokumen SOP kegiatan utama yang telah disahkan\nb. Laporan monitoring & evaluasi penerapan SOP berkala\nc. Berita acara revisi SOP jika terdapat perubahan proses bisnis",
                                'catatan_tpi' => 'Pastikan SOP memuat alur waktu, biaya (Rp 0 jika bebas biaya), dan penanggung jawab.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'A2-IND-02',
                        'nama_indikator' => 'Sistem Pemerintahan Berbasis Elektronik (SPBE / E-Gov)',
                        'aspek' => AspekRb::Reform,
                        'keterangan_juknis' => 'Pemanfaatan sistem digital terintegrasi dalam manajemen persuratan, kepegawaian, dan pelayanan publik.',
                        'urutan' => 2,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Pemanfaatan Sistem Informasi Manajemen Terpadu',
                                'checklist_daduk' => "a. Bukti pemanfaatan aplikasi persuratan dinas elektronik (SISUMAKER/SRIKANDI)\nb. Bukti integrasi sistem informasi layanan publik\nc. Screenshot dashboard pemantauan pelayanan secara real-time",
                                'catatan_tpi' => 'Lengkapi dengan user log atau statistik utilisasi aplikasi.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-A3',
                'nama_area' => 'III. Penataan Sistem Manajemen SDM',
                'urutan' => 3,
                'indikators' => [
                    [
                        'kode' => 'A3-IND-01',
                        'nama_indikator' => 'Pengembangan Pegawai Berbasis Kompetensi & Pola Mutasi Internal',
                        'aspek' => AspekRb::Reform,
                        'keterangan_juknis' => 'Pelaksanaan rotasi berkala dan diklat/pelatihan pengembangan kapasitas aparatur.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Pelaksanaan Rotasi / Mutasi Internal Pegawai',
                                'checklist_daduk' => "a. Pola mutasi/rotasi internal berkala\nb. SK mutasi/rotasi staf antar unit/pos\nc. Notula rapat Tim Penilai Kinerja (TPK)",
                                'catatan_tpi' => 'Rotasi ditujukan untuk penyegaran dan pencegahan kejenuhan serta potensi penyimpangan.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'A3-IND-02',
                        'nama_indikator' => 'Penegakan Disiplin dan Kode Etik Pegawai',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Penerapan aturan disiplin, rekapitulasi absensi elektronik, dan penjatuhan sanksi jika ada pelanggaran.',
                        'urutan' => 2,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Monitoring dan Rekapitulasi Absensi serta Penegakan Disiplin',
                                'checklist_daduk' => "a. Rekap absensi finger/aplikasi SIMPEG bulanan\nb. Tindak lanjut keterlambatan / ketidakhadiran pegawai\nc. Laporan majelis kode etik (jika ada)",
                                'catatan_tpi' => 'Persentase kehadiran harus terdokumentasi rapi setiap bulan.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-A4',
                'nama_area' => 'IV. Penguatan Akuntabilitas Kinerja',
                'urutan' => 4,
                'indikators' => [
                    [
                        'kode' => 'A4-IND-01',
                        'nama_indikator' => 'Keterlibatan Pimpinan dalam Akuntabilitas Kinerja',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Keterlibatan langsung Kepala Satker dalam penyusunan Renstra, IKU, dan Perjanjian Kinerja.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Keterlibatan Pimpinan dalam Penetapan dan Pemantauan Kinerja',
                                'checklist_daduk' => "a. Dokumen Perjanjian Kinerja (PK) berjenjang ditandatangani pimpinan\nb. Notula rapat monitoring capaian kinerja triwulanan dipimpin Kakanwil/Ka.UPT\nc. Laporan Kinerja Instansi Pemerintah (LKjIP) tahunan",
                                'catatan_tpi' => 'Sertakan foto pimpinan memimpin rapat evaluasi capaian kinerja triwulanan.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-A5',
                'nama_area' => 'V. Penguatan Pengawasan',
                'urutan' => 5,
                'indikators' => [
                    [
                        'kode' => 'A5-IND-01',
                        'nama_indikator' => 'Pengendalian Gratifikasi dan Unit Pengendalian Gratifikasi (UPG)',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Public campaign tolak gratifikasi, pembentukan UPG satker, dan pelaporan gratifikasi.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Pembentukan UPG dan Kampanye Publik Tolak Gratifikasi',
                                'checklist_daduk' => "a. SK Penetapan Unit Pengendalian Gratifikasi (UPG) Satker\nb. Banner/spanduk/banner tolak gratifikasi dan pungli di area pelayanan publik\nc. Laporan rekapitulasi penerimaan/penolakan gratifikasi bulanan",
                                'catatan_tpi' => 'Wajib ada kanal pelaporan gratifikasi yang mudah diakses pengunjung.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'A5-IND-02',
                        'nama_indikator' => 'Penanganan Pengaduan Masyarakat (WBS & SP4N-LAPOR!)',
                        'aspek' => AspekRb::Reform,
                        'keterangan_juknis' => 'Pemanfaatan aplikasi SP4N-LAPOR!, kotak pengaduan, dan tindak lanjut aduan.',
                        'urutan' => 2,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Penerapan Sistem Pengaduan Masyarakat Terpadu dan WBS',
                                'checklist_daduk' => "a. SK Pejabat Pengelola Pengaduan\nb. Tanda terima atau rekap penanganan aduan SP4N-LAPOR!\nc. Dokumentasi sarana pengaduan (kotak aduan, WhatsApp info, loket)",
                                'catatan_tpi' => 'Setiap aduan wajib ditindaklanjuti maksimal dalam batas SOP (contoh 3-5 hari kerja).',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-A6',
                'nama_area' => 'VI. Peningkatan Kualitas Pelayanan Publik',
                'urutan' => 6,
                'indikators' => [
                    [
                        'kode' => 'A6-IND-01',
                        'nama_indikator' => 'Standar Pelayanan Publik dan Maklumat Pelayanan',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Penyusunan standar pelayanan yang melibatkan pengguna dan maklumat pelayanan terpampang jelas.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Penyusunan dan Publikasi Standar Pelayanan & Maklumat Pelayanan',
                                'checklist_daduk' => "a. Dokumen Standar Pelayanan Publik (SPP) seluruh jenis layanan\nb. Foto maklumat pelayanan terpampang di ruang tunggu utama\nc. Laporan Forum Konsultasi Publik (FKP) bersama stakeholder",
                                'catatan_tpi' => 'FKP wajib melibatkan perwakilan masyarakat, akademisi, atau media.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'A6-IND-02',
                        'nama_indikator' => 'Inovasi Pelayanan Publik dan Fasilitas Kelompok Rentan',
                        'aspek' => AspekRb::Reform,
                        'keterangan_juknis' => 'Penyediaan inovasi unggulan dan sarana ramah disabilitas, lansia, ibu menyusui.',
                        'urutan' => 2,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Penyediaan Fasilitas Layanan Ramah HAM & Kelompok Rentan',
                                'checklist_daduk' => "a. Foto jalur pemandu (guiding block), ramp kursi roda, toilet disabilitas\nb. Ruang laktasi / pojok ASI dan area bermain anak\nc. Buku pedoman atau SOP perlakuan khusus kelompok rentan",
                                'catatan_tpi' => 'Fasilitas kelompok rentan harus berfungsi aktif dan bersih.',
                            ],
                        ],
                    ],
                ],
            ],
            // Dokumen Tambahan
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Pengungkit,
                'kode' => 'LKE-DOK-TAMBAHAN',
                'nama_area' => 'VII. SPTJM, Video Profil, & Bahan Paparan',
                'urutan' => 7,
                'indikators' => [
                    [
                        'kode' => 'DOK-IND-01',
                        'nama_indikator' => 'Kelengkapan Dokumen Wajib Usulan WBK/WBBM',
                        'aspek' => AspekRb::Pemenuhan,
                        'keterangan_juknis' => 'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM), tautan video profil, dan presentasi paparan pimpinan.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'SPTJM, Video Profil Satker, dan Bahan Paparan Pimpinan',
                                'checklist_daduk' => "a. SPTJM bermaterai ditandatangani Kepala Satuan Kerja\nb. Tautan video profil pembangunan ZI (YouTube / Cloud Drive)\nc. Bahan tayang paparan pimpinan (format PPTX/PDF maksimal 20 slide)",
                                'catatan_tpi' => 'Pastikan video profil menampilkan testimoni penerima layanan nyata.',
                            ],
                        ],
                    ],
                ],
            ],
            // ==========================================
            // KOMPONEN HASIL (2 AREA)
            // ==========================================
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Hasil,
                'kode' => 'LKE-HASIL-1',
                'nama_area' => 'Hasil I: Birokrasi yang Bersih dan Akuntabel',
                'urutan' => 8,
                'indikators' => [
                    [
                        'kode' => 'H1-IND-01',
                        'nama_indikator' => 'Survei Persepsi Korupsi (IPK) dan Capaian SAKIP',
                        'aspek' => AspekRb::Hasil,
                        'keterangan_juknis' => 'Nilai survei IPK mandiri/eksternal dan predikat evaluasi SAKIP satker.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Laporan Hasil Survei IPK dan Nilai Evaluasi SAKIP',
                                'checklist_daduk' => "a. Laporan hasil survei IPK triwulanan (minimal predikat Baik)\nb. LHE SAKIP dari Inspektorat Jenderal\nc. Rencana aksi tindak lanjut rekomendasi hasil survei",
                                'catatan_tpi' => 'Minimal responden survei mencukupi batas metodologi.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'kategori' => KategoriRb::LkeWbkWbbm,
                'komponen' => KomponenRb::Hasil,
                'kode' => 'LKE-HASIL-2',
                'nama_area' => 'Hasil II: Pelayanan Publik yang Prima',
                'urutan' => 9,
                'indikators' => [
                    [
                        'kode' => 'H2-IND-01',
                        'nama_indikator' => 'Survei Kepuasan Masyarakat (IKM) & Indeks Pelayanan Publik',
                        'aspek' => AspekRb::Hasil,
                        'keterangan_juknis' => 'Hasil survei IKM dan pengakuan/penghargaan eksternal pelayanan publik.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Laporan Hasil Survei Kepuasan Masyarakat (IKM)',
                                'checklist_daduk' => "a. Laporan berkala survei kepuasan masyarakat (aplikasi 3AS/Balitbangham)\nb. Publikasi nilai IKM di papan pengumuman/medsos\nc. Dokumentasi penghargaan eksternal (jika ada)",
                                'catatan_tpi' => 'Nilai IKM minimal berkategori Sangat Baik atau Memuaskan.',
                            ],
                        ],
                    ],
                ],
            ],
            // ==========================================
            // RKT RB GENERAL (MAKRO KEMENKUM)
            // ==========================================
            [
                'kategori' => KategoriRb::RktGeneral,
                'komponen' => KomponenRb::None,
                'kode' => 'RKT-GEN',
                'nama_area' => 'RKT Reformasi Birokrasi General',
                'urutan' => 10,
                'indikators' => [
                    [
                        'kode' => 'RKT-IND-01',
                        'nama_indikator' => 'Peningkatan Indeks SPBE & Digitalisasi Layanan',
                        'aspek' => AspekRb::None,
                        'keterangan_juknis' => 'Pelaksanaan arsitektur SPBE dan interoperabilitas data kementerian.',
                        'urutan' => 1,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Pemenuhan Standar Keamanan Informasi dan Pemanfaatan SPBE Satker',
                                'checklist_daduk' => "a. Bukti audit keamanan SPBE / CSIRT\nb. Laporan integrasi data satker ke portal Kanwil",
                                'catatan_tpi' => 'Pastikan domain resmi go.id dan sertifikat SSL aktif.',
                            ],
                        ],
                    ],
                    [
                        'kode' => 'RKT-IND-02',
                        'nama_indikator' => 'Tingkat Maturitas SPIP Terintegrasi',
                        'aspek' => AspekRb::None,
                        'keterangan_juknis' => 'Penyusunan risk register (daftar risiko) dan monitoring pengendalian intern.',
                        'urutan' => 2,
                        'sub_indikators' => [
                            [
                                'nomor_poin' => 1,
                                'judul_poin' => 'Dokumen Risk Register dan Rencana Pengendalian Risiko',
                                'checklist_daduk' => "a. Dokumen register risiko seluruh bidang operasional\nb. Laporan pemantauan pengendalian risiko triwulanan",
                                'catatan_tpi' => 'Lengkapi dengan mitigasi risiko operasional utama.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($areas as $areaData) {
            $indikators = $areaData['indikators'];
            unset($areaData['indikators']);

            $area = RbArea::firstOrCreate(['kode' => $areaData['kode']], $areaData);

            foreach ($indikators as $indData) {
                $subIndikators = $indData['sub_indikators'];
                unset($indData['sub_indikators']);

                $indData['rb_area_id'] = $area->id;
                $indikator = RbIndikator::firstOrCreate(['kode' => $indData['kode']], $indData);

                foreach ($subIndikators as $subData) {
                    $subData['rb_indikator_id'] = $indikator->id;
                    RbSubIndikator::firstOrCreate(
                        [
                            'rb_indikator_id' => $indikator->id,
                            'nomor_poin' => $subData['nomor_poin'],
                        ],
                        $subData
                    );
                }
            }
        }
    }
}
