<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAKAYUH — Kanwil Kemenkumham Kalsel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0C2B64;
            --primary-dark: #081B3F;
            --gold: #C8993D;
            --gold-light: #F7E7C4;
            --bg-page: #F8FAFC;
            --text-dark: #0F172A;
            --text-muted: #64748B;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #081B3F 0%, #0C2B64 50%, #163E8A 100%);
            min-height: 100vh;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            width: 100%;
            max-width: 960px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: linear-gradient(90deg, #081B3F 0%, #0C2B64 100%);
            padding: 32px 40px;
            color: #ffffff;
            position: relative;
            border-bottom: 4px solid var(--gold);
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .brand-badge {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--gold);
            color: #081B3F;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(200, 153, 61, 0.4);
        }
        .title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 14px;
            color: #CBD5E1;
            margin-top: 4px;
        }
        .content {
            padding: 36px 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }
        @media (max-width: 768px) {
            .content { grid-template-columns: 1fr; }
        }
        .card {
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 24px;
            background: #F8FAFC;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .card:hover {
            border-color: #CBD5E1;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
        }
        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .card-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            gap: 8px;
        }
        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        .btn-gold {
            background: var(--gold);
            color: #081B3F;
        }
        .btn-gold:hover {
            background: #b5872e;
        }
        .credentials-section {
            grid-column: 1 / -1;
            border-top: 1px solid #E2E8F0;
            padding-top: 24px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
        }
        .badge-live {
            background: #DCFCE7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-live::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22C55E;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th {
            background: #F1F5F9;
            text-align: left;
            padding: 10px 14px;
            color: #475569;
            font-weight: 600;
            border-bottom: 1px solid #E2E8F0;
        }
        td {
            padding: 10px 14px;
            border-bottom: 1px solid #F1F5F9;
            color: #334155;
        }
        tr:last-child td { border-bottom: none; }
        .role-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11.5px;
        }
        .role-admin { background: #E0E7FF; color: #3730A3; }
        .role-kanwil { background: #FEF3C7; color: #92400E; }
        .role-satker { background: #E0F2FE; color: #075985; }
        .role-viewer { background: #F1F5F9; color: #475569; }
        .footer {
            background: #F8FAFC;
            border-top: 1px solid #E2E8F0;
            padding: 16px 40px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-brand">
                <img src="/logo-pengayoman.png" class="brand-badge" style="padding: 0; object-fit: cover; border-radius: 14px;" alt="Logo Pengayoman">
                <div>
                    <h1 class="title">BAKAYUH</h1>
                    <p class="subtitle">Sistem Terpadu Akuntabilitas Kinerja & Reformasi Birokrasi — Kanwil Kemenkumham Kalsel</p>
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Card 1: Frontend Application -->
            <div class="card">
                <div>
                    <div class="card-title">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Aplikasi Web (Frontend UI)
                    </div>
                    <p class="card-desc">
                        Antarmuka web interaktif Single Page Application (Vue 3 + Vite) untuk IKU, Renaksi TW I–IV, SAKIP 17 Satker, LKE ZI 2026, dan Dashboard Eksekutif.
                    </p>
                </div>
                <a href="http://127.0.0.1:5173" target="_blank" class="btn btn-gold">
                    Buka Aplikasi Web (Port 5173) &rarr;
                </a>
            </div>

            <!-- Card 2: Swagger Documentation -->
            <div class="card">
                <div>
                    <div class="card-title">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        Dokumentasi API (Swagger)
                    </div>
                    <p class="card-desc">
                        Dokumentasi interaktif OpenAPI 3.0 dengan 48 endpoint REST API lengkap beserta schema request/response dan otentikasi Sanctum Bearer Token.
                    </p>
                </div>
                <a href="/api/documentation" class="btn btn-primary">
                    Buka Swagger UI &rarr;
                </a>
            </div>

            <!-- Credentials Section -->
            <div class="credentials-section">
                <div class="section-header">
                    <h2 class="section-title">Akun Pengguna Bawaan (Default Login Credentials)</h2>
                    <span class="badge-live">Database MySQL Terhubung</span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Peran (Role)</th>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th>Satker / Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="role-pill role-admin">Super Admin</span></td>
                                <td>Super Administrator</td>
                                <td><code>superadmin@kemenkum.go.id</code></td>
                                <td><code>password123</code></td>
                                <td>Kanwil Kalsel (Akses Penuh)</td>
                            </tr>
                            <tr>
                                <td><span class="role-pill role-kanwil">Admin Kanwil</span></td>
                                <td>Tim Verifikator RB</td>
                                <td><code>verifikator@kemenkum.go.id</code></td>
                                <td><code>password123</code></td>
                                <td>Kanwil Kalsel (Verifikasi & Nilai)</td>
                            </tr>
                            <tr>
                                <td><span class="role-pill role-satker">Operator Satker</span></td>
                                <td>Operator Lapas Bjm</td>
                                <td><code>operator.lpbjm@kemenkum.go.id</code></td>
                                <td><code>password123</code></td>
                                <td>Lapas Banjarmasin (Input Data)</td>
                            </tr>
                            <tr>
                                <td><span class="role-pill role-viewer">Pimpinan</span></td>
                                <td>Kepala Kantor Wilayah</td>
                                <td><code>pimpinan@kemenkum.go.id</code></td>
                                <td><code>password123</code></td>
                                <td>Kanwil Kalsel (Executive View)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer">
            &copy; 2026 Kantor Wilayah Kementerian Hukum dan HAM Kalimantan Selatan &bull; Sistem Terpadu BAKAYUH v1.0.0
        </div>
    </div>
</body>
</html>
