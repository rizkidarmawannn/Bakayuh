<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rencana Aksi - {{ $renaksi->nama_aksi }}</title>
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
        table.meta {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        table.meta td {
            padding: 5px 6px;
            font-size: 9.5pt;
            vertical-align: top;
        }
        .section-box {
            border: 1px solid #ccc;
            padding: 10px 14px;
            margin-bottom: 15px;
            font-size: 9.5pt;
            background-color: #fafafa;
        }
        .section-box h4 {
            margin: 0 0 6px 0;
            font-size: 10pt;
            color: #0c2b64;
        }
        table.files {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.files th, table.files td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 9pt;
        }
        table.files th {
            background-color: #f2f4f8;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>KEMENTERIAN HUKUM REPUBLIK INDONESIA</h3>
        <h4>KANTOR WILAYAH KALIMANTAN SELATAN</h4>
        <p>Jalan Brigjen H. Hasan Basry No. 42 Banjarmasin 70123 | Laman: kalsel.kemenkumham.go.id</p>
    </div>

    <div class="title">
        <h2>LAPORAN CAPAIAN RENCANA AKSI PERJANJIAN KINERJA</h2>
        <p style="margin: 3px 0 0 0; font-size: 10pt;">Periode: {{ $renaksi->triwulan->label() }} - TA {{ $renaksi->tahunAnggaran->tahun }}</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 25%;"><strong>Satuan Kerja</strong></td>
            <td style="width: 2%;">:</td>
            <td><strong>{{ $renaksi->satker->nama }}</strong> ({{ $renaksi->satker->kode }})</td>
        </tr>
        <tr>
            <td><strong>Indikator Kinerja</strong></td>
            <td>:</td>
            <td>[{{ $renaksi->indikator->kode }}] {{ $renaksi->indikator->nama }}</td>
        </tr>
        <tr>
            <td><strong>Rencana Aksi</strong></td>
            <td>:</td>
            <td><strong>{{ $renaksi->nama_aksi }}</strong></td>
        </tr>
        <tr>
            <td><strong>Target Output</strong></td>
            <td>:</td>
            <td>{{ $renaksi->target_output ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Status Verifikasi</strong></td>
            <td>:</td>
            <td><strong>{{ strtoupper($renaksi->realisasi?->status?->label() ?? 'BELUM LAPOR') }}</strong></td>
        </tr>
    </table>

    <div class="section-box">
        <h4>Uraian Realisasi Pelaksanaan:</h4>
        <p>{{ $renaksi->realisasi?->deskripsi_realisasi ?? 'Belum ada uraian realisasi.' }}</p>
        <p style="margin-top: 8px;"><strong>Persentase Selesai:</strong> {{ $renaksi->realisasi?->persentase_selesai ?? 0 }}%</p>
    </div>

    <div class="section-box">
        <h4>Daftar Dokumen Bukti Dukung:</h4>
        @if($renaksi->realisasi && $renaksi->realisasi->buktiDukung->count() > 0)
            <table class="files">
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th>Nama Berkas</th>
                        <th style="width: 25%;">Ukuran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($renaksi->realisasi->buktiDukung as $idx => $doc)
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td>{{ $doc->nama_file }}</td>
                            <td style="text-align: center;">{{ round($doc->ukuran_file / 1024, 1) }} KB</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #666; font-style: italic;">Tidak ada berkas bukti dukung yang dilampirkan.</p>
        @endif
    </div>

    @if($renaksi->realisasi?->catatan_verifikasi)
    <div class="section-box">
        <h4>Catatan Tim Verifikator Kanwil:</h4>
        <p>{{ $renaksi->realisasi->catatan_verifikasi }}</p>
    </div>
    @endif

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Kepala Satuan Kerja,</strong><br>
                <br><br><br><br>
                <strong><u>{{ $renaksi->satker->nama }}</u></strong>
            </td>
            <td>
                Banjarmasin, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Verifikator Kantor Wilayah,</strong><br>
                <br><br><br><br>
                <strong><u>{{ $renaksi->realisasi?->verifikator?->name ?? 'Tim Verifikator Kanwil' }}</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>
