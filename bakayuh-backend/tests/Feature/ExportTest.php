<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Models\IndikatorKinerja;
use App\Models\EvaluasiSakip;
use App\Models\RencanaAksi;
use App\Enums\PredikatSakip;
use App\Enums\Triwulan;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SatuanKerja $satker;
    protected TahunAnggaran $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->user = User::where('email', 'superadmin@kemenkum.go.id')->first();
        $this->satker = SatuanKerja::where('kode', 'LP-BJM')->first();
        $this->tahun = TahunAnggaran::getAktif();

        // Seed an evaluation
        EvaluasiSakip::create([
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'nilai_perencanaan' => 85.5,
            'nilai_pengukuran' => 84.0,
            'nilai_pelaporan' => 88.0,
            'nilai_evaluasi' => 82.0,
            'nilai_total' => 84.55,
            'predikat' => PredikatSakip::A,
            'catatan' => 'Implementasi akuntabilitas kinerja sangat memadai.',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_export_iku_excel()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/export/iku/excel?tahun_anggaran_id=' . $this->tahun->id);

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-disposition') ?? '', '.xlsx'));
    }

    public function test_export_sakip_excel()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/export/sakip/excel?tahun_anggaran_id=' . $this->tahun->id);

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-disposition') ?? '', '.xlsx'));
    }

    public function test_export_sakip_pdf()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/export/sakip/pdf/{$this->satker->id}?tahun_anggaran_id=" . $this->tahun->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_export_renaksi_pdf()
    {
        $ind = IndikatorKinerja::first();
        $renaksi = RencanaAksi::create([
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'indikator_id' => $ind->id,
            'nama_aksi' => 'Uji Coba Laporan PDF Renaksi',
            'triwulan' => Triwulan::TW1,
            'target_output' => '1 Dokumen Laporan Kinerja',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/export/renaksi/pdf/{$renaksi->id}");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
