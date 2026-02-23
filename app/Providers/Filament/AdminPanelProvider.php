<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\KasusResource\Pages\ListKasuses;
use App\Filament\Resources\PetugasResource\Pages\ListPetugas;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->brandLogo(fn() => view('filament.brand'))
            ->brandName('SIM-LP')
            ->favicon(asset('logo.png'))
            ->passwordReset()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn(): string => view('filament.hooks.topbar-greeting', [
                    'user' => Filament::auth()->user(),
                ])->render(),
            )
            ->renderHook(
                TablesRenderHook::TOOLBAR_SEARCH_BEFORE,
                fn(): string => <<<'HTML'
                    <style>
                        @media (min-width: 1024px) {
                            .fi-resource-kasus .fi-ta-header-toolbar .fi-ta-search-field,
                            .fi-resource-petugas .fi-ta-header-toolbar .fi-ta-search-field {
                                width: 34rem;
                            }

                            .fi-resource-kasus .fi-ta-header-toolbar .fi-ta-search-field .fi-input-wrp,
                            .fi-resource-petugas .fi-ta-header-toolbar .fi-ta-search-field .fi-input-wrp {
                                width: 100%;
                            }
                        }
                    </style>
                HTML,
                scopes: [
                    ListKasuses::class,
                    ListPetugas::class,
                ],
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
