<?php

namespace Tests\Feature;

use App\Enums\DokumenStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Kasus;
use App\Models\Perkara;
use App\Models\Petugas;
use App\Models\Satker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_petugas_and_related_kasus_entities(): void
    {
        $satker = Satker::query()->create([
            'nama' => 'Satker Audit',
            'tipe' => 'subdit',
            'kode' => 'SATKER-AUDIT',
        ]);

        $perkara = Perkara::query()->create([
            'nama' => 'KTP',
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'name' => 'Admin Audit',
            'email' => 'admin.audit@example.com',
            'password' => 'password',
            'role' => UserRole::Admin,
            'satker_id' => $satker->id,
        ]);

        $this->actingAs($user);

        $kasus = Kasus::query()->create([
            'satker_id' => $satker->id,
            'nomor_lp' => 'LP/AUDIT/001/2026',
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
        ]);

        $petugas = Petugas::query()->create([
            'satker_id' => $satker->id,
            'nama' => 'Petugas Audit',
            'nrp' => '12345',
            'pangkat' => 'IPDA',
            'no_hp' => '081200000001',
        ]);
        $petugas->update(['pangkat' => 'AKP']);
        $petugas->delete();

        $rtl = $kasus->rtls()->create([
            'tanggal' => now()->toDateString(),
            'keterangan' => 'RTL awal',
        ]);
        $rtl->update(['keterangan' => 'RTL diperbarui']);
        $rtl->delete();

        $korban = $kasus->korbans()->create([
            'nama' => 'Korban Audit',
            'tempat_lahir' => 'Mataram',
            'tanggal_lahir' => now()->subYears(20)->toDateString(),
            'alamat' => 'Alamat Korban',
            'hp' => '081200000002',
        ]);
        $korban->update(['nama' => 'Korban Audit Update']);
        $korban->delete();

        $pelaku = $kasus->tersangkas()->create([
            'nama' => 'Tersangka Audit',
            'tempat_lahir' => 'Mataram',
            'tanggal_lahir' => now()->subYears(30)->toDateString(),
            'alamat' => 'Alamat Tersangka',
            'hp' => '081200000003',
        ]);
        $pelaku->update(['nama' => 'Tersangka Audit Update']);
        $pelaku->delete();

        $saksi = $kasus->saksis()->create([
            'nama' => 'Saksi Audit',
            'tempat_lahir' => 'Mataram',
            'tanggal_lahir' => now()->subYears(25)->toDateString(),
            'alamat' => 'Alamat Saksi',
            'hp' => '081200000004',
        ]);
        $saksi->update(['nama' => 'Saksi Audit Update']);
        $saksi->delete();

        $this->assertLogged('petugas', 'create');
        $this->assertLogged('petugas', 'update');
        $this->assertLogged('petugas', 'delete');

        $petugasUpdateLog = AuditLog::query()
            ->where('module', 'petugas')
            ->where('action', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($petugasUpdateLog);
        $this->assertSame('IPDA', $petugasUpdateLog->changes['Pangkat']['old'] ?? null);
        $this->assertSame('AKP', $petugasUpdateLog->changes['Pangkat']['new'] ?? null);

        $this->assertLogged('rtl', 'create');
        $this->assertLogged('rtl', 'update');
        $this->assertLogged('rtl', 'delete');

        $this->assertLogged('kasus_korban', 'create');
        $this->assertLogged('kasus_korban', 'update');
        $this->assertLogged('kasus_korban', 'delete');

        $this->assertLogged('kasus_pelaku', 'create');
        $this->assertLogged('kasus_pelaku', 'update');
        $this->assertLogged('kasus_pelaku', 'delete');

        $this->assertLogged('kasus_saksi', 'create');
        $this->assertLogged('kasus_saksi', 'update');
        $this->assertLogged('kasus_saksi', 'delete');
    }

    private function assertLogged(string $module, string $action): void
    {
        $this->assertTrue(
            AuditLog::query()
                ->where('module', $module)
                ->where('action', $action)
                ->exists(),
            "Expected audit log for module {$module} and action {$action}."
        );
    }
}
