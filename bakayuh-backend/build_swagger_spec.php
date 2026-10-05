<?php

$spec = [
    'openapi' => '3.0.0',
    'info' => [
        'title' => 'BAKAYUH REST API Documentation',
        'description' => "Dokumentasi Resmi REST API Sistem BAKAYUH (Sistem Terpadu E-Performance & E-RB) Kantor Wilayah Kementerian Hukum Kalimantan Selatan.\n\nSistem ini mencakup manajemen IKU (Perjanjian Kinerja), Rencana Aksi Triwulanan (Renaksi TW I - IV), Evaluasi SAKIP (4 Komponen), Instrumen LKE WBK/WBBM 2026, RKT RB (General, Tematik, Meso), Workspace Data Dukung (Daduk), Ruang Klarifikasi Dua Arah, dan Ekspor Resmi Excel/PDF.",
        'version' => '1.0.0',
        'contact' => [
            'name' => 'Tim Pengelola TI Kanwil Kalsel',
            'email' => 'superadmin@kemenkum.go.id',
        ],
    ],
    'servers' => [
        [
            'url' => 'http://127.0.0.1:8000/api',
            'description' => 'Development Server Localhost',
        ],
    ],
    'components' => [
        'securitySchemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'Sanctum Token',
                'description' => 'Masukkan token otentikasi Sanctum setelah login.',
            ],
        ],
        'schemas' => [
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'role' => ['type' => 'string', 'enum' => ['super_admin', 'admin_kanwil', 'operator_satker', 'viewer']],
                    'satker_id' => ['type' => 'integer', 'nullable' => true],
                    'is_active' => ['type' => 'boolean'],
                ],
            ],
            'SatuanKerja' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'kode' => ['type' => 'string'],
                    'nama' => ['type' => 'string'],
                    'tipe' => ['type' => 'string', 'enum' => ['kanwil', 'upt', 'satker']],
                    'is_active' => ['type' => 'boolean'],
                ],
            ],
            'TahunAnggaran' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'tahun' => ['type' => 'integer'],
                    'is_aktif' => ['type' => 'boolean'],
                ],
            ],
            'IndikatorKinerja' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'kode' => ['type' => 'string'],
                    'nama' => ['type' => 'string'],
                    'satuan' => ['type' => 'string'],
                    'polaritas' => ['type' => 'string', 'enum' => ['positif', 'negatif']],
                    'level' => ['type' => 'string', 'enum' => ['strategis', 'program', 'kegiatan']],
                ],
            ],
            'EvaluasiSakip' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'satker_id' => ['type' => 'integer'],
                    'tahun_anggaran_id' => ['type' => 'integer'],
                    'nilai_perencanaan' => ['type' => 'number', 'format' => 'float'],
                    'nilai_pengukuran' => ['type' => 'number', 'format' => 'float'],
                    'nilai_pelaporan' => ['type' => 'number', 'format' => 'float'],
                    'nilai_evaluasi' => ['type' => 'number', 'format' => 'float'],
                    'nilai_total' => ['type' => 'number', 'format' => 'float'],
                    'predikat' => ['type' => 'string', 'enum' => ['AA', 'A', 'BB', 'B', 'CC', 'C', 'D']],
                    'catatan' => ['type' => 'string', 'nullable' => true],
                ],
            ],
        ],
    ],
    'tags' => [
        ['name' => 'Autentikasi & Akun', 'description' => 'Login, logout, profil, dan pembaruan kata sandi'],
        ['name' => 'Dashboard & Portal Publik', 'description' => 'Metrik ringkasan eksekutif dan grafik visual'],
        ['name' => 'Master Data', 'description' => 'Pengelolaan Satuan Kerja, Tahun Anggaran, dan Indikator IKU'],
        ['name' => 'IKU (Perjanjian Kinerja)', 'description' => 'Penetapan target IKU, pelaporan realisasi, dan matriks capaian'],
        ['name' => 'Rencana Aksi (Renaksi)', 'description' => 'Pemantauan aksi triwulanan TW I - IV dan berkas bukti (10MB)'],
        ['name' => 'SAKIP (Akuntabilitas)', 'description' => 'Evaluasi 4 komponen (30/30/15/25), perbandingan satker, dan tren'],
        ['name' => 'E-RB & LKE ZI 2026', 'description' => '6 Pengungkit + 2 Hasil, Target Periode, Daduk PDF 50MB, dan Chat Klarifikasi'],
        ['name' => 'Manajemen Pengguna', 'description' => 'Pengelolaan user oleh Super Admin, reset password, dan status aktif'],
        ['name' => 'Ekspor Dokumen', 'description' => 'Ekspor resmi ke format Excel (.xlsx) dan PDF (.pdf)'],
    ],
    'paths' => [],
];

// Helper to add paths
function addRoute(&$spec, $path, $method, $tag, $summary, $params = [], $reqBody = null, $responses = [200 => ['description' => 'Operasi berhasil']], $auth = true) {
    if (!isset($spec['paths'][$path])) {
        $spec['paths'][$path] = [];
    }

    $op = [
        'tags' => [$tag],
        'summary' => $summary,
        'parameters' => $params,
        'responses' => $responses,
    ];

    if ($reqBody) {
        $op['requestBody'] = $reqBody;
    }

    if ($auth) {
        $op['security'] = [['bearerAuth' => []]];
    }

    $spec['paths'][$path][strtolower($method)] = $op;
}

// 1. Auth & Profile
addRoute($spec, '/auth/login', 'POST', 'Autentikasi & Akun', 'Masuk ke sistem dengan email & password', [], [
    'required' => true,
    'content' => [
        'application/json' => [
            'schema' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'example' => 'superadmin@kemenkum.go.id'],
                    'password' => ['type' => 'string', 'example' => 'password123'],
                ],
            ],
        ],
    ],
], [200 => ['description' => 'Login berhasil, mengembalikan token Sanctum']], false);

addRoute($spec, '/auth/me', 'GET', 'Autentikasi & Akun', 'Ambil data profil pengguna yang sedang login');
addRoute($spec, '/auth/logout', 'POST', 'Autentikasi & Akun', 'Keluar dan cabut token otentikasi Sanctum');
addRoute($spec, '/auth/password', 'PATCH', 'Autentikasi & Akun', 'Perbarui kata sandi akun mandiri', [], [
    'required' => true,
    'content' => [
        'application/json' => [
            'schema' => [
                'type' => 'object',
                'required' => ['current_password', 'password', 'password_confirmation'],
                'properties' => [
                    'current_password' => ['type' => 'string'],
                    'password' => ['type' => 'string', 'minLength' => 8],
                    'password_confirmation' => ['type' => 'string'],
                ],
            ],
        ],
    ],
]);

// 2. Dashboard & Publik
addRoute($spec, '/dashboard/summary', 'GET', 'Dashboard & Portal Publik', 'Ringkasan metrik IKU, Renaksi, SAKIP, dan LKE ZI', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/dashboard/charts', 'GET', 'Dashboard & Portal Publik', 'Data analitik visual untuk grafik ApexCharts (Radar SAKIP, Donut LKE, Bar Renaksi)', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/publik/summary', 'GET', 'Dashboard & Portal Publik', 'Ringkasan transparansi kinerja instansi untuk portal publik tanpa login', [], null, [200 => ['description' => 'Publik data']], false);

// 3. Master Satuan Kerja
addRoute($spec, '/satker', 'GET', 'Master Data', 'Daftar seluruh Satker / UPT se-Kalsel');
addRoute($spec, '/satker', 'POST', 'Master Data', 'Tambah Satuan Kerja baru', [], [
    'required' => true,
    'content' => [
        'application/json' => [
            'schema' => [
                'type' => 'object',
                'required' => ['kode', 'nama', 'tipe'],
                'properties' => [
                    'kode' => ['type' => 'string', 'example' => 'LP-BJM'],
                    'nama' => ['type' => 'string', 'example' => 'Lapas Kelas IIA Banjarmasin'],
                    'tipe' => ['type' => 'string', 'enum' => ['kanwil', 'upt', 'satker']],
                ],
            ],
        ],
    ],
]);
addRoute($spec, '/satker/{id}', 'GET', 'Master Data', 'Detail Satuan Kerja', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/satker/{id}', 'PUT', 'Master Data', 'Perbarui Satuan Kerja', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/satker/{id}', 'DELETE', 'Master Data', 'Hapus Satuan Kerja', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 4. Master Tahun Anggaran
addRoute($spec, '/tahun-anggaran', 'GET', 'Master Data', 'Daftar Tahun Anggaran');
addRoute($spec, '/tahun-anggaran', 'POST', 'Master Data', 'Tambah Tahun Anggaran baru');
addRoute($spec, '/tahun-anggaran/{id}/set-aktif', 'PATCH', 'Master Data', 'Set tahun anggaran sebagai acuan aktif default sistem', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 5. Master Indikator Kinerja
addRoute($spec, '/indikator-kinerja', 'GET', 'Master Data', 'Daftar Master Indikator Kinerja (IKU)');
addRoute($spec, '/indikator-kinerja', 'POST', 'Master Data', 'Tambah Indikator Kinerja baru');
addRoute($spec, '/indikator-kinerja/{id}', 'GET', 'Master Data', 'Detail Indikator Kinerja', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/indikator-kinerja/{id}', 'PUT', 'Master Data', 'Perbarui Indikator Kinerja', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/indikator-kinerja/{id}', 'DELETE', 'Master Data', 'Hapus Indikator Kinerja', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 6. IKU Target & Realisasi
addRoute($spec, '/target-iku/matriks', 'GET', 'IKU (Perjanjian Kinerja)', 'Matriks capaian IKU seluruh Satker dengan kode warna status', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/target-iku', 'GET', 'IKU (Perjanjian Kinerja)', 'List target IKU per Satker & Tahun');
addRoute($spec, '/target-iku', 'POST', 'IKU (Perjanjian Kinerja)', 'Tetapkan target IKU');
addRoute($spec, '/target-iku/{id}', 'PUT', 'IKU (Perjanjian Kinerja)', 'Perbarui nilai target IKU', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/realisasi-iku', 'GET', 'IKU (Perjanjian Kinerja)', 'Daftar laporan realisasi IKU');
addRoute($spec, '/realisasi-iku', 'POST', 'IKU (Perjanjian Kinerja)', 'Input laporan realisasi IKU & auto kalkulasi persentase');
addRoute($spec, '/realisasi-iku/{id}', 'PUT', 'IKU (Perjanjian Kinerja)', 'Perbarui realisasi IKU', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 7. Rencana Aksi (Renaksi TW I - IV)
addRoute($spec, '/rencana-aksi', 'GET', 'Rencana Aksi (Renaksi)', 'Daftar rencana aksi triwulanan', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
    ['name' => 'satker_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
    ['name' => 'triwulan', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['TW1', 'TW2', 'TW3', 'TW4']]],
]);
addRoute($spec, '/rencana-aksi', 'POST', 'Rencana Aksi (Renaksi)', 'Buat Rencana Aksi baru');
addRoute($spec, '/rencana-aksi/{id}', 'GET', 'Rencana Aksi (Renaksi)', 'Detail Rencana Aksi', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/rencana-aksi/{id}', 'PUT', 'Rencana Aksi (Renaksi)', 'Perbarui Rencana Aksi', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/rencana-aksi/{id}', 'DELETE', 'Rencana Aksi (Renaksi)', 'Hapus Rencana Aksi', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

addRoute($spec, '/realisasi-renaksi', 'POST', 'Rencana Aksi (Renaksi)', 'Simpan draf realisasi pelaksanaan & progres %');
addRoute($spec, '/realisasi-renaksi/{id}', 'PUT', 'Rencana Aksi (Renaksi)', 'Perbarui draf realisasi', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/realisasi-renaksi/{id}/submit', 'PATCH', 'Rencana Aksi (Renaksi)', 'Operator mengajukan laporan ke Kanwil (Wajib ada minimal 1 bukti)', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/realisasi-renaksi/{id}/verify', 'PATCH', 'Rencana Aksi (Renaksi)', 'Admin Kanwil verifikasi (terverifikasi / perlu_perbaikan)', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/bukti-dukung-renaksi', 'POST', 'Rencana Aksi (Renaksi)', 'Unggah berkas bukti dukung (Maksimal 10MB - PDF, JPG, PNG)', [], [
    'required' => true,
    'content' => [
        'multipart/form-data' => [
            'schema' => [
                'type' => 'object',
                'required' => ['realisasi_renaksi_id', 'file'],
                'properties' => [
                    'realisasi_renaksi_id' => ['type' => 'integer'],
                    'file' => ['type' => 'string', 'format' => 'binary'],
                ],
            ],
        ],
    ],
]);
addRoute($spec, '/bukti-dukung-renaksi/{id}', 'DELETE', 'Rencana Aksi (Renaksi)', 'Hapus berkas bukti dukung renaksi', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 8. SAKIP
addRoute($spec, '/evaluasi-sakip/perbandingan', 'GET', 'SAKIP (Akuntabilitas)', 'Tabel komparasi nilai 4 komponen SAKIP 17 Satker se-Kalsel', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/evaluasi-sakip/tren', 'GET', 'SAKIP (Akuntabilitas)', 'Data tren capaian nilai SAKIP lintas tahun', [
    ['name' => 'satker_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/evaluasi-sakip', 'GET', 'SAKIP (Akuntabilitas)', 'Daftar evaluasi SAKIP');
addRoute($spec, '/evaluasi-sakip', 'POST', 'SAKIP (Akuntabilitas)', 'Input nilai 4 komponen SAKIP (Auto kalkulasi bobot 30/30/15/25 & predikat AA-D)');
addRoute($spec, '/evaluasi-sakip/{id}', 'PUT', 'SAKIP (Akuntabilitas)', 'Perbarui nilai evaluasi SAKIP', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 9. E-RB & LKE ZI 2026
addRoute($spec, '/rb-area', 'GET', 'E-RB & LKE ZI 2026', 'Hirarki Area, Indikator, dan Sub-Indikator (LKE WBK/WBBM 2026 atau RKT)', [
    ['name' => 'kategori', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['lke_wbk_wbbm', 'rkt_general', 'rkt_tematik', 'rkt_meso']]],
    ['name' => 'komponen', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['pengungkit', 'hasil']]],
]);
addRoute($spec, '/rb-area/progress', 'GET', 'E-RB & LKE ZI 2026', 'Kalkulasi progres pemenuhan data dukung ZI / RKT', [
    ['name' => 'satker_id', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
    ['name' => 'periode', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['B03', 'B06', 'B09', 'B12']]],
]);
addRoute($spec, '/rb-target-periode', 'GET', 'E-RB & LKE ZI 2026', 'Daftar target periode & status daduk');
addRoute($spec, '/rb-target-periode/get-or-init', 'POST', 'E-RB & LKE ZI 2026', 'Ambil atau inisialisasi target sub-indikator per periode', [], [
    'required' => true,
    'content' => [
        'application/json' => [
            'schema' => [
                'type' => 'object',
                'required' => ['rb_sub_indikator_id', 'tahun_anggaran_id', 'satker_id', 'periode'],
                'properties' => [
                    'rb_sub_indikator_id' => ['type' => 'integer'],
                    'tahun_anggaran_id' => ['type' => 'integer'],
                    'satker_id' => ['type' => 'integer'],
                    'periode' => ['type' => 'string', 'enum' => ['B03', 'B06', 'B09', 'B12']],
                ],
            ],
        ],
    ],
]);
addRoute($spec, '/rb-target-periode/{id}', 'GET', 'E-RB & LKE ZI 2026', 'Detail workspace daduk sub-indikator (dengan countdown)', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/rb-target-periode/{id}', 'PUT', 'E-RB & LKE ZI 2026', 'Perbarui penjelasan inovasi ZI atau batas waktu upload', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/rb-target-periode/{id}/submit', 'PATCH', 'E-RB & LKE ZI 2026', 'Satker ajukan data dukung untuk diverifikasi Kanwil', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/rb-target-periode/{id}/verify', 'PATCH', 'E-RB & LKE ZI 2026', 'Kanwil verifikasi data dukung (Auto-lock workspace jika status Lengkap/Tercapai)', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

addRoute($spec, '/rb-dokumen-daduk', 'POST', 'E-RB & LKE ZI 2026', 'Unggah berkas PDF data dukung (Maksimal 50MB - PDF only)', [], [
    'required' => true,
    'content' => [
        'multipart/form-data' => [
            'schema' => [
                'type' => 'object',
                'required' => ['rb_target_periode_id', 'file'],
                'properties' => [
                    'rb_target_periode_id' => ['type' => 'integer'],
                    'file' => ['type' => 'string', 'format' => 'binary'],
                ],
            ],
        ],
    ],
]);
addRoute($spec, '/rb-dokumen-daduk/{id}', 'DELETE', 'E-RB & LKE ZI 2026', 'Hapus berkas PDF data dukung (hanya jika workspace tidak terkunci)', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/rb-catatan-verifikasi', 'GET', 'E-RB & LKE ZI 2026', 'Daftar pesan ruang diskusi / klarifikasi dua arah', [
    ['name' => 'rb_target_periode_id', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/rb-catatan-verifikasi', 'POST', 'E-RB & LKE ZI 2026', 'Kirim pesan klarifikasi baru');

// 10. Users Management
addRoute($spec, '/users', 'GET', 'Manajemen Pengguna', 'List pengguna sistem (Super Admin only)', [
    ['name' => 'role', 'in' => 'query', 'schema' => ['type' => 'string']],
    ['name' => 'satker_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
    ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
]);
addRoute($spec, '/users', 'POST', 'Manajemen Pengguna', 'Tambah pengguna baru (Super Admin only)');
addRoute($spec, '/users/{id}', 'GET', 'Manajemen Pengguna', 'Detail pengguna', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/users/{id}', 'PUT', 'Manajemen Pengguna', 'Perbarui data pengguna', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/users/{id}', 'DELETE', 'Manajemen Pengguna', 'Hapus pengguna', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/users/{id}/toggle-active', 'PATCH', 'Manajemen Pengguna', 'Aktifkan atau nonaktifkan akun pengguna', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);
addRoute($spec, '/users/{id}/reset-password', 'PATCH', 'Manajemen Pengguna', 'Reset kata sandi pengguna oleh Super Admin', [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]]);

// 11. Ekspor
addRoute($spec, '/export/iku/excel', 'GET', 'Ekspor Dokumen', 'Unduh lembar kerja Matriks IKU format Excel (.xlsx)', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/export/sakip/excel', 'GET', 'Ekspor Dokumen', 'Unduh lembar komparasi nilai SAKIP format Excel (.xlsx)', [
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/export/sakip/pdf/{satkerId}', 'GET', 'Ekspor Dokumen', 'Unduh Lembar Hasil Evaluasi (LHE) SAKIP resmi format PDF (.pdf)', [
    ['name' => 'satkerId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
    ['name' => 'tahun_anggaran_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
]);
addRoute($spec, '/export/renaksi/pdf/{id}', 'GET', 'Ekspor Dokumen', 'Unduh Laporan Capaian Rencana Aksi resmi format PDF (.pdf)', [
    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
]);

// Write output JSON
$json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents('storage/api-docs/api-docs.json', $json);
echo "OpenAPI 3.0 specification successfully generated at storage/api-docs/api-docs.json\n";
echo "Total documented endpoints: " . count($spec['paths']) . "\n";
