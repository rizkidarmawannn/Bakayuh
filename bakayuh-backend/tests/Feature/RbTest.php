<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Models\RbArea;
use App\Models\RbSubIndikator;
use App\Models\RbTargetPeriode;
use App\Enums\UserRole;
use App\Enums\StatusVerifikasiRb;
use App\Enums\PeriodeRb;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RbTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $operatorSatker;
    protected User $verifikator;
    protected SatuanKerja $satker;
    protected TahunAnggaran $tahun;
    protected RbSubIndikator $subIndikator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        Storage::fake('public');

        $this->superAdmin = User::where('email', 'superadmin@kemenkum.go.id')->first();
        $this->verifikator = User::where('email', 'verifikator@kemenkum.go.id')->first();
        $this->operatorSatker = User::where('email', 'operator.lpbjm@kemenkum.go.id')->first();
        $this->satker = SatuanKerja::where('kode', 'LP-BJM')->first();
        $this->tahun = TahunAnggaran::getAktif();

        // Get first seeded sub-indikator
        $this->subIndikator = RbSubIndikator::firstOrFail();
    }

    public function test_can_list_rb_areas()
    {
        $response = $this->actingAs($this->operatorSatker, 'sanctum')
            ->getJson('/api/rb-area?kategori=lke_wbk_wbbm');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'kategori', 'komponen', 'kode', 'nama_area']]]);
    }

    public function test_get_or_init_rb_target_periode()
    {
        $response = $this->actingAs($this->operatorSatker, 'sanctum')
            ->postJson('/api/rb-target-periode/get-or-init', [
                'rb_sub_indikator_id' => $this->subIndikator->id,
                'tahun_anggaran_id' => $this->tahun->id,
                'satker_id' => $this->satker->id,
                'periode' => 'B03',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.periode', 'B03')
            ->assertJsonPath('data.status_verifikasi', 'belum_upload');
    }

    public function test_upload_and_auto_lock_on_verification()
    {
        // 1. Init target
        $target = RbTargetPeriode::create([
            'rb_sub_indikator_id' => $this->subIndikator->id,
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'periode' => PeriodeRb::B03,
            'status_verifikasi' => StatusVerifikasiRb::BelumUpload,
        ]);

        // 2. Upload PDF daduk
        $file = UploadedFile::fake()->create('daduk_sk_tim.pdf', 200, 'application/pdf');

        $uploadRes = $this->actingAs($this->operatorSatker, 'sanctum')
            ->postJson('/api/rb-dokumen-daduk', [
                'rb_target_periode_id' => $target->id,
                'file' => $file,
            ]);

        $uploadRes->assertStatus(201)
            ->assertJsonPath('data.nama_file', 'daduk_sk_tim.pdf');

        $dokumenId = $uploadRes->json('data.id');

        // 3. Submit for verification
        $submitRes = $this->actingAs($this->operatorSatker, 'sanctum')
            ->patchJson("/api/rb-target-periode/{$target->id}/submit");

        $submitRes->assertStatus(200)
            ->assertJsonPath('data.status_verifikasi', 'belum_verif');

        // 4. Verifikator approves as Lengkap (auto-lock)
        $verifyRes = $this->actingAs($this->verifikator, 'sanctum')
            ->patchJson("/api/rb-target-periode/{$target->id}/verify", [
                'status_verifikasi' => 'lengkap',
                'catatan' => 'Dokumen SK lengkap dan telah diverifikasi sah.',
            ]);

        $verifyRes->assertStatus(200)
            ->assertJsonPath('data.status_verifikasi', 'lengkap');

        // 5. Verify auto-lock prevents new upload
        $file2 = UploadedFile::fake()->create('daduk_extra.pdf', 100, 'application/pdf');
        $lockedUploadRes = $this->actingAs($this->operatorSatker, 'sanctum')
            ->postJson('/api/rb-dokumen-daduk', [
                'rb_target_periode_id' => $target->id,
                'file' => $file2,
            ]);

        $lockedUploadRes->assertStatus(422); // Rejection because workspace is locked!

        // 6. Clarification thread exists
        $notesRes = $this->actingAs($this->operatorSatker, 'sanctum')
            ->getJson("/api/rb-catatan-verifikasi?rb_target_periode_id={$target->id}");

        $notesRes->assertStatus(200)
            ->assertJsonFragment(['pesan' => 'Dokumen SK lengkap dan telah diverifikasi sah.']);
    }

    public function test_two_way_clarification_thread()
    {
        $target = RbTargetPeriode::create([
            'rb_sub_indikator_id' => $this->subIndikator->id,
            'tahun_anggaran_id' => $this->tahun->id,
            'satker_id' => $this->satker->id,
            'periode' => PeriodeRb::B06,
            'status_verifikasi' => StatusVerifikasiRb::BelumUpload,
        ]);

        // Verifikator asks question
        $res1 = $this->actingAs($this->verifikator, 'sanctum')
            ->postJson('/api/rb-catatan-verifikasi', [
                'rb_target_periode_id' => $target->id,
                'pesan' => 'Mohon lampiran tanda tangan berita acara dilengkapi.',
            ]);
        $res1->assertStatus(201);

        // Operator replies
        $res2 = $this->actingAs($this->operatorSatker, 'sanctum')
            ->postJson('/api/rb-catatan-verifikasi', [
                'rb_target_periode_id' => $target->id,
                'pesan' => 'Siap, dokumen berita acara bertanda tangan sedang disiapkan.',
            ]);
        $res2->assertStatus(201);

        // Check messages count
        $listRes = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson("/api/rb-catatan-verifikasi?rb_target_periode_id={$target->id}");

        $listRes->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
