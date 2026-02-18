<?php

namespace Tests\Feature;

use App\Enums\DokumenStatus;
use App\Enums\UserRole;
use App\Filament\Resources\KasusResource;
use App\Models\Kasus;
use App\Models\Perkara;
use App\Models\Satker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseNotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    private int $lpCounter = 1;

    public function test_kasus_create_sends_notifications_to_expected_recipients(): void
    {
        [$satkerA, $satkerB, $perkara, $users] = $this->makeBaseContext();

        $this->actingAs($users['actor_admin_a']);

        $kasus = $this->createKasus($satkerA, $perkara);

        $expectedUrl = KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin');
        $expectedBody = sprintf('Kasus %s dibuat.', $kasus->nomor_lp);

        $this->assertUserNotification($users['super_admin'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($users['atasan'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($users['admin_a_other'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertSame(0, $users['actor_admin_a']->notifications()->count());
        $this->assertSame(0, $users['admin_b']->notifications()->count());
        $this->assertSame($satkerB->id, $users['admin_b']->satker_id);
    }

    public function test_kasus_update_sends_notification_for_general_updates(): void
    {
        [$satkerA, $satkerB, $perkara, $users] = $this->makeBaseContext();

        $this->actingAs($users['actor_admin_a']);

        $kasus = $this->createKasus($satkerA, $perkara, withEvents: false);
        $kasus->update([
            'hubungan_pelaku_dengan_korban' => 'Tetangga',
        ]);

        $expectedUrl = KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin');
        $expectedBody = sprintf('Kasus %s diperbarui.', $kasus->nomor_lp);

        $this->assertUserNotification($users['super_admin'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($users['atasan'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($users['admin_a_other'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertSame(0, $users['actor_admin_a']->notifications()->count());
        $this->assertSame(0, $users['admin_b']->notifications()->count());
        $this->assertSame($satkerB->id, $users['admin_b']->satker_id);
    }

    public function test_kasus_delete_sends_notification_with_index_link(): void
    {
        [$satkerA, $satkerB, $perkara, $users] = $this->makeBaseContext();

        $this->actingAs($users['actor_admin_a']);

        $kasus = $this->createKasus($satkerA, $perkara, withEvents: false);
        $nomorLp = $kasus->nomor_lp;

        $kasus->delete();

        $expectedUrl = KasusResource::getUrl(panel: 'admin');
        $expectedBody = sprintf('Kasus %s dihapus.', $nomorLp);

        $this->assertUserNotification($users['super_admin'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($users['atasan'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($users['admin_a_other'], 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertSame(0, $users['actor_admin_a']->notifications()->count());
        $this->assertSame(0, $users['admin_b']->notifications()->count());
        $this->assertSame($satkerB->id, $users['admin_b']->satker_id);
    }

    public function test_rtl_create_update_delete_send_notifications(): void
    {
        [$satkerA, $satkerB, $perkara, $users] = $this->makeBaseContext();

        $this->actingAs($users['actor_admin_a']);

        $kasus = $this->createKasus($satkerA, $perkara, withEvents: false);
        $expectedUrl = KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin');

        $rtl = $kasus->rtls()->create([
            'tanggal' => now()->toDateString(),
            'keterangan' => 'RTL awal',
        ]);

        $createBody = sprintf('RTL pada kasus %s ditambahkan.', $kasus->nomor_lp);
        $this->assertUserNotification($users['super_admin'], 1, 'Timeline RTL', $createBody, $expectedUrl);
        $this->assertUserNotification($users['atasan'], 1, 'Timeline RTL', $createBody, $expectedUrl);
        $this->assertUserNotification($users['admin_a_other'], 1, 'Timeline RTL', $createBody, $expectedUrl);

        $rtl->update([
            'keterangan' => 'RTL diperbarui',
        ]);

        $updateBody = sprintf('RTL pada kasus %s diperbarui.', $kasus->nomor_lp);
        $this->assertUserNotification($users['super_admin'], 2, 'Timeline RTL', $updateBody, $expectedUrl);
        $this->assertUserNotification($users['atasan'], 2, 'Timeline RTL', $updateBody, $expectedUrl);
        $this->assertUserNotification($users['admin_a_other'], 2, 'Timeline RTL', $updateBody, $expectedUrl);

        $rtl->delete();

        $deleteBody = sprintf('RTL pada kasus %s dihapus.', $kasus->nomor_lp);
        $this->assertUserNotification($users['super_admin'], 3, 'Timeline RTL', $deleteBody, $expectedUrl);
        $this->assertUserNotification($users['atasan'], 3, 'Timeline RTL', $deleteBody, $expectedUrl);
        $this->assertUserNotification($users['admin_a_other'], 3, 'Timeline RTL', $deleteBody, $expectedUrl);

        $this->assertSame(0, $users['actor_admin_a']->notifications()->count());
        $this->assertSame(0, $users['admin_b']->notifications()->count());
        $this->assertSame($satkerB->id, $users['admin_b']->satker_id);
    }

    public function test_actor_is_excluded_from_notification_recipients(): void
    {
        $satker = Satker::query()->create([
            'nama' => 'Satker Actor',
            'tipe' => 'subdit',
            'kode' => 'SATKER-ACTOR',
        ]);
        $perkara = $this->createPerkara();

        $actorSuperAdmin = $this->createUser('Super Admin Actor', 'super.actor@example.com', UserRole::SuperAdmin);
        $otherSuperAdmin = $this->createUser('Super Admin Other', 'super.other@example.com', UserRole::SuperAdmin);
        $atasan = $this->createUser('Atasan Actor Test', 'atasan.actor@example.com', UserRole::Atasan);
        $adminSatker = $this->createUser('Admin Satker Actor Test', 'admin.actor.satker@example.com', UserRole::Admin, $satker->id);

        $this->actingAs($actorSuperAdmin);

        $kasus = $this->createKasus($satker, $perkara);

        $expectedUrl = KasusResource::getUrl('view', ['record' => $kasus], panel: 'admin');
        $expectedBody = sprintf('Kasus %s dibuat.', $kasus->nomor_lp);

        $this->assertSame(0, $actorSuperAdmin->notifications()->count());
        $this->assertUserNotification($otherSuperAdmin, 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($atasan, 1, 'Data Kasus', $expectedBody, $expectedUrl);
        $this->assertUserNotification($adminSatker, 1, 'Data Kasus', $expectedBody, $expectedUrl);
    }

    /**
     * @return array{0: Satker, 1: Satker, 2: Perkara, 3: array<string, User>}
     */
    private function makeBaseContext(): array
    {
        $satkerA = Satker::query()->create([
            'nama' => 'Satker A Notification',
            'tipe' => 'subdit',
            'kode' => 'SATKER-A-NOTIF',
        ]);
        $satkerB = Satker::query()->create([
            'nama' => 'Satker B Notification',
            'tipe' => 'subdit',
            'kode' => 'SATKER-B-NOTIF',
        ]);

        $perkara = $this->createPerkara();

        return [
            $satkerA,
            $satkerB,
            $perkara,
            [
                'super_admin' => $this->createUser('Super Admin Notification', 'super.notification@example.com', UserRole::SuperAdmin),
                'atasan' => $this->createUser('Atasan Notification', 'atasan.notification@example.com', UserRole::Atasan),
                'actor_admin_a' => $this->createUser('Admin A Actor Notification', 'admin.a.actor@example.com', UserRole::Admin, $satkerA->id),
                'admin_a_other' => $this->createUser('Admin A Other Notification', 'admin.a.other@example.com', UserRole::Admin, $satkerA->id),
                'admin_b' => $this->createUser('Admin B Notification', 'admin.b.notification@example.com', UserRole::Admin, $satkerB->id),
            ],
        ];
    }

    private function createPerkara(): Perkara
    {
        return Perkara::query()->create([
            'nama' => 'Perkara Notification',
            'is_active' => true,
        ]);
    }

    private function createUser(string $name, string $email, UserRole $role, ?int $satkerId = null): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'satker_id' => $satkerId,
        ]);
    }

    private function createKasus(Satker $satker, Perkara $perkara, bool $withEvents = true): Kasus
    {
        $payload = [
            'satker_id' => $satker->id,
            'nomor_lp' => sprintf('LP/%s/%03d/2026', $satker->kode, $this->lpCounter++),
            'tanggal_lp' => now()->toDateString(),
            'perkara_id' => $perkara->id,
            'dokumen_status' => DokumenStatus::Lidik->value,
            'penyelesaian_id' => null,
        ];

        if ($withEvents) {
            return Kasus::query()->create($payload);
        }

        return Kasus::withoutEvents(fn (): Kasus => Kasus::query()->create($payload));
    }

    private function assertUserNotification(
        User $user,
        int $expectedCount,
        string $expectedTitle,
        string $expectedBody,
        string $expectedUrl,
    ): void {
        $notifications = $user->notifications()->get();

        $this->assertSame($expectedCount, $notifications->count());

        $matchedNotification = $notifications->first(function ($notification) use ($expectedTitle, $expectedBody, $expectedUrl): bool {
            return ($notification->data['format'] ?? null) === 'filament'
                && ($notification->data['title'] ?? null) === $expectedTitle
                && ($notification->data['body'] ?? null) === $expectedBody
                && data_get($notification->data, 'actions.0.url') === $expectedUrl;
        });

        $this->assertNotNull($matchedNotification);
    }
}
