<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 90;

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->isSuperAdmin() || $user?->isAdmin() || $user?->isAtasan();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Aktor')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('satker.nama')
                    ->label('Satker')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        'login' => 'info',
                        'logout' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('summary')
                    ->label('Ringkasan')
                    ->wrap()
                    ->limit(90),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->options([
                        'kasus' => 'Kasus',
                        'rtl' => 'RTL',
                        'kasus_korban' => 'Kasus Korban',
                        'kasus_pelaku' => 'Kasus Pelaku',
                        'kasus_saksi' => 'Kasus Saksi',
                        'petugas' => 'Petugas',
                        'auth' => 'Autentikasi',
                    ]),
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'login' => 'Login',
                        'logout' => 'Logout',
                    ]),
                Tables\Filters\SelectFilter::make('satker_id')
                    ->label('Satker')
                    ->relationship('satker', 'nama')
                    ->visible(fn (): bool => Auth::user()?->isSuperAdmin() ?? false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Audit')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime('d-m-Y H:i:s'),
                        Infolists\Components\TextEntry::make('actor.name')->label('Aktor')->default('-'),
                        Infolists\Components\TextEntry::make('satker.nama')->label('Satker')->default('-'),
                        Infolists\Components\TextEntry::make('module')
                            ->label('Modul')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('action')
                            ->label('Aksi')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'create' => 'success',
                                'update' => 'warning',
                                'delete' => 'danger',
                                'login' => 'info',
                                'logout' => 'gray',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('summary')->label('Ringkasan')->columnSpanFull(),
                        Infolists\Components\TextEntry::make('changes')
                            ->label('Perubahan')
                            ->state(fn (AuditLog $record): string => json_encode($record->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('meta')
                            ->label('Metadata')
                            ->state(fn (AuditLog $record): string => json_encode($record->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['actor:id,name', 'satker:id,nama']);
        $user = Auth::user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        if (($user?->isAdmin() || $user?->isAtasan()) && $user->satker_id) {
            return $query->where('satker_id', $user->satker_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
