<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_request_password_reset_link(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $requestResetUrl = route('filament.admin.auth.password-reset.request', absolute: false);

        $this->get(route('filament.admin.auth.login'))
            ->assertOk()
            ->assertSee($requestResetUrl, false);
    }

    public function test_request_password_reset_sends_notification(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $user = User::factory()->create([
            'email' => 'reset.user@example.com',
        ]);

        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm([
                'email' => $user->email,
            ])
            ->call('request');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }
}
