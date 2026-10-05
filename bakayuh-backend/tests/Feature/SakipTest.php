<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Enums\PredikatSakip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;

class SakipTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SatuanKerja $satker;
    protected TahunAnggaran $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'verifikator@kemenkum.go.id')->first();
        $this->satker = SatuanKerja::where('kode', 'LP-BJM')->first();
        $this->tahun = TahunAnggaran::getAktif();
    }

    public function test_sakip_calculation_and_predikat(): void
    {
        // Nilai:
        // Perencanaan: 85 (30% -> 25.50)
        // Pengukuran: 80 (30% -> 24.00)
        // Pelaporan: 90 (15% -> 13.50)
        // Evaluasi: 88 (25% -> 22.00)
        // Total = 25.50 + 24.00 + 13.50 + 22.00 = 85.00 -> Predikat A
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/evaluasi-sakip', [
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'nilai_perencanaan' => 85.00,
            'nilai_pengukuran' => 80.00,
            'nilai_pelaporan' => 90.00,
            'nilai_evaluasi' => 88.00,
            'catatan' => 'Peningkatan yang sangat baik dibanding tahun lalu.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.nilai_total', '85.00')
            ->assertJsonPath('data.predikat', PredikatSakip::A->value);
    }

    public function test_sakip_perbandingan_endpoint(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/evaluasi-sakip/perbandingan');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['satker_id', 'kode', 'nama', 'tipe', 'nilai_total', 'predikat'],
                ],
            ]);
    }
}