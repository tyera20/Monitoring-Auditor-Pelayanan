<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringPenugasanFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_users_seeded_with_expected_role(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['username' => 'guest', 'role' => 'guest']);
    }

    public function test_guest_role_cannot_write_penugasan(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $petugas = Petugas::query()->create([
            'name' => 'Pak Yusuf',
            'nip' => '19880010001',
            'position' => 'Verifikator',
        ]);
        $layanan = Layanan::query()->create([
            'nama_layanan' => 'Verifikator TKDN',
        ]);

        $response = $this->actingAs($guest)->post(route('penugasan.store'), [
            'petugas_ids' => [$petugas->id],
            'layanan_id' => $layanan->id,
            'task_detail' => 'Percobaan data',
            'tempat' => 'Jakarta',
            'komoditi' => 'Beras',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('tr_penugasan', 0);
    }

    public function test_admin_can_store_penugasan_with_multiple_petugas(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrator',
            'username' => 'admin-test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $petugasA = Petugas::query()->create([
            'name' => 'Pak Yusuf',
            'nip' => '19880010001',
            'position' => 'Verifikator',
        ]);
        $petugasB = Petugas::query()->create([
            'name' => 'Bu Ani',
            'nip' => '19880010002',
            'position' => 'Verifikator',
        ]);
        $layanan = Layanan::query()->create([
            'nama_layanan' => 'Verifikator TKDN',
        ]);

        $response = $this->actingAs($admin)->post(route('penugasan.store'), [
            'petugas_ids' => [$petugasA->id, $petugasB->id],
            'layanan_id' => $layanan->id,
            'task_detail' => 'Verifikasi lapangan',
            'tempat' => 'Jakarta',
            'komoditi' => 'Beras',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('tr_penugasan', 1);
        $this->assertDatabaseCount('penugasan_petugas', 2);
    }
}
