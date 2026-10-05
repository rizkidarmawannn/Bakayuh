<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Models\IndikatorKinerja;
use App\Models\RencanaAksi;
use App\Models\RealisasiRenaksi;
use App\Enums\Triwulan;
use App\Enums\StatusRenaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\DatabaseSeeder;

class RenaksiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected SatuanKerja $satker;
    protected TahunAnggaran $tahun;
    protected IndikatorKinerja $indikator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'superadmin@kemenkum.go.id')->first();
        $this->operator = User::where('email', 'operator.lpbjm@kemenkum.go.id')->first();
        $this->satker = SatuanKerja::where('kode', 'LP-BJM')->first();
        $this->tahun = TahunAnggaran::getAktif();
        $this->indikator = IndikatorKinerja::where('kode', 'IKU-01')->first();
    }

    public function test_full_renaksi_lifecycle(): void
    {
        Storage::fake('public');

        // 1. Buat Rencana Aksi TW1
        $resCreate = $this->actingAs($this->admin, 'sanctum')->postJson('/api/rencana-aksi', [
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'indikator_id' => $this->indikator->id,
            'nama_aksi' => 'Sosialisasi dan Pembentukan Pokja ZI Menuju WBK',
            'triwulan' => Triwulan::TW1->value,
            'target_output' => 'Terbentuknya 6 tim pokja dengan SK Kepala Satker',
        ]);

        $resCreate->assertStatus(201);
        $renaksiId = $resCreate->json('data.id');

        // 2. Operator submit laporan realisasi (belum ada file)
        $resReal = $this->actingAs($this->operator, 'sanctum')->postJson('/api/realisasi-renaksi', [
            'rencana_aksi_id' => $renaksiId,
            'deskripsi_realisasi' => 'Telah dilaksanakan sosialisasi dan SK pokja telah terbit.',
            'persentase_selesai' => 100,
        ]);

        $resReal->assertStatus(201);
        $realisasiId = $resReal->json('data.id');

        // 3. Upload file bukti dukung
        $file = UploadedFile::fake()->create('laporan_pokja.pdf', 500, 'application/pdf');
        $resUpload = $this->actingAs($this->operator, 'sanctum')->postJson('/api/bukti-dukung-renaksi', [
            'realisasi_renaksi_id' => $realisasiId,
            'file' => $file,
        ]);

        $resUpload->assertStatus(201);

        // 4. Operator ajukan verifikasi (PATCH)
        $resSubmit = $this->actingAs($this->operator, 'sanctum')
            ->patchJson("/api/realisasi-renaksi/{$realisasiId}/submit");

        $resSubmit->assertStatus(200)
            ->assertJsonPath('data.status', StatusRenaksi::MenungguVerifikasi->value);

        // 5. Admin verifikasi (PATCH)
        $resVerify = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/realisasi-renaksi/{$realisasiId}/verify", [
                'status' => StatusRenaksi::Terverifikasi->value,
                'catatan_verifikasi' => 'Dokumen lengkap dan valid.',
            ]);

        $resVerify->assertStatus(200)
            ->assertJsonPath('data.status', StatusRenaksi::Terverifikasi->value);
    }
}