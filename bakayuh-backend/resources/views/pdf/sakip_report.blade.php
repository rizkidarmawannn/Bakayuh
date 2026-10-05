<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Evaluasi SAKIP - {{ $evaluasi->satker->nama }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            margin: 20px 30px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header h4 {
            margin: 3px 0 0 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #444;
        }
        .title {
            text-align: center;
            margin: 15px 0 20px 0;
        }
        .title h2 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
        }
        .title p {
            margin: 3px 0 0 0;
            font-size: 10pt;
            color: #555;
        }
        table.meta {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        table.meta td {
            padding: 4px 6px;
            font-size: 10pt;
            vertical-align: top;
        }
        table.score {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.score th, table.score td {
            border: 1px solid #333;
            padding: 7px 10px;
            font-size: 9.5pt;
        }
        table.score th {
            background-color: #f2f4f8;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .predikat-box {
            border: 2px solid #0c2b64;
            background-color: #f0f4fc;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .predikat-box h1 {
            margin: 0;
            font-size: 24pt;
            color: #0c2b64;
        }
        .predikat-box p {
            margin: 2px 0 0 0;
            font-weight: bold;
            font-size: 11pt;
            color: #333;
        }
        .notes-box {
            border: 1px solid #ccc;
            background-color: #fafafa;
            padding: 10px 14px;
            margin-bottom: 30px;
            font-size: 9.5pt;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <!-- Kop Surat Kemenkum RI Kanwil Kalsel -->
    <div class="header">
        <h3>KEMENTERIAN HUKUM REPUBLIK INDONESIA</h3>
        <h4>KANTOR WILAYAH KALIMANTAN SELATAN</h4>
        <p>Jalan Brigjen H. Hasan Basry No. 42 Banjarmasin 70123 | Laman: kalsel.kemenkumham.go.id</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="title">
        <h2>LEMBAR HASIL EVALUASI AKUNTABILITAS KINERJA (SAKIP)</h2>
        <p>Tahun Anggaran {{ $evaluasi->tahunAnggaran->tahun }}</p>
    </div>

    <!-- Identitas Satker -->
    <table class="meta">
        <tr>
            <td style="width: 25%;"><strong>Satuan Kerja</strong></td>
            <td style="width: 2%;">:</td>
            <td><strong>{{ $evaluasi->satker->nama }}</strong></td>
        </tr>
        <tr>
            <td><strong>Kode Satker</strong></td>
            <td>:</td>
            <td>{{ $evaluasi->satker->kode }} ({{ strtoupper($evaluasi->satker->tipe->value) }})</td>
        </tr>
        <tr>
            <td><strong>Tanggal Penilaian</strong></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($evaluasi->updated_at)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <!-- Hasil Nilai Predikat Box -->
    <div class="predikat-box">
        <p>NILAI AKHIR SAKIP: <strong style="font-size: 16pt; color: #0c2b64;">{{ $evaluasi->nilai_total }}</strong> / 100</p>
        <h1>{{ $evaluasi->predikat->value }}</h1>
        <p>Predikat: {{ $evaluasi->predikat->label() }}</p>
    </div>

    <!-- Tabel 4 Komponen Evaluasi -->
    <table class="score">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th>Komponen Penilaian SAKIP</th>
                <th style="width: 15%;">Bobot</th>
                <th style="width: 18%;">Nilai Capaian</th>
                <th style="width: 18%;">Kontribusi Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Perencanaan Kinerja</td>
                <td class="text-center">30%</td>
                <td class="text-center font-bold">{{ $evaluasi->nilai_perencanaan }}</td>
                <td class="text-center font-bold">{{ number_format($evaluasi->nilai_perencanaan * 0.30, 2) }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Pengukuran Kinerja</td>
                <td class="text-center">30%</td>
                <td class="text-center font-bold">{{ $evaluasi->nilai_pengukuran }}</td>
                <td class="text-center font-bold">{{ number_format($evaluasi->nilai_pengukuran * 0.30, 2) }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Pelaporan Kinerja</td>
                <td class="text-center">15%</td>
                <td class="text-center font-bold">{{ $evaluasi->nilai_pelaporan }}</td>
                <td class="text-center font-bold">{{ number_format($evaluasi->nilai_pelaporan * 0.15, 2) }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Evaluasi Akuntabilitas Kinerja Internal</td>
                <td class="text-center">25%</td>
                <td class="text-center font-bold">{{ $evaluasi->nilai_evaluasi }}</td>
                <td class="text-center font-bold">{{ number_format($evaluasi->nilai_evaluasi * 0.25, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f4f8;">
                <td colspan="2" class="text-right font-bold">TOTAL NILAI KINERJA (SAKIP):</td>
                <td class="text-center font-bold">100%</td>
                <td colspan="2" class="text-center font-bold" style="font-size: 11pt; color: #0c2b64;">
                    {{ $evaluasi->nilai_total }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Catatan & Rekomendasi -->
    <div class="notes-box">
        <strong>Catatan & Rekomendasi Tim Evaluator Kanwil:</strong><br>
        <p style="margin-top: 4px;">{{ $evaluasi->catatan ?? 'Telah memenuhi standar evaluasi akuntabilitas kinerja instansi pemerintah tahun anggaran berjalan.' }}</p>
    </div>

    <!-- Tanda Tangan Resmi -->
    <table class="signature-table">
        <tr>
            <td></td>
            <td style="text-align: center;">
                Banjarmasin, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Kepala Bagian Program dan Humas</strong><br>
                Selaku Koordinator Tim SAKIP Kanwil,<br>
                <br><br><br><br>
                <strong><u>TIM EVALUATOR SAKIP KANWIL</u></strong><br>
                NIP. 197805122003121001
            </td>
        </tr>
    </table>
</body>
</html>
