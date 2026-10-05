<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Models\IndikatorKinerja;
use App\Models\TargetIku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;

class IkuTest extends TestCase
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

    public function test_admin_can_set_target_iku(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/target-iku', [
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'indikator_id' => $this->indikator->id,
            'nilai_target' => 85.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.nilai_target', '85.0000');
    }

    public function test_operator_can_input_realisasi_with_auto_percentage(): void
    {
        $target = TargetIku::create([
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'indikator_id' => $this->indikator->id,
            'nilai_target' => 100.00,
        ]);

        $response = $this->actingAs($this->operator, 'sanctum')->postJson('/api/realisasi-iku', [
            'target_iku_id' => $target->id,
            'nilai_realisasi' => 95.00,
            'keterangan' => 'Capaian TW3',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.persentase_capaian', '95.0000');
    }

    public function test_matriks_capaian_iku_endpoint(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/target-iku/matriks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'satker' => ['id', 'kode', 'nama', 'tipe'],
                        'indikators' => [
                            '*' => ['indikator_id', 'kode', 'nama', 'satuan', 'target', 'realisasi', 'persentase', 'status_color'],
                        ],
                    ],
                ],
            ]);
    }
}