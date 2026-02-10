<?php

namespace App\Filament\Resources;

use App\Enums\DokumenStatus;
use App\Filament\Resources\KasusResource\Pages;
use App\Filament\Resources\KasusResource\RelationManagers\RtlsRelationManager;
use App\Models\Kasus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class KasusResource extends Resource
{
    protected static ?string $model = Kasus::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'kasus';

    protected static ?string $modelLabel = 'Kasus';

    protected static ?string $pluralModelLabel = 'Kasus';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->isSuperAdmin() || $user?->isAdmin() || $user?->isAtasan();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Kasus')
                    ->schema([
                        Forms\Components\Select::make('satker_id')
                            ->relationship('satker', 'nama')
                            ->label('Satker')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => Auth::user()?->satker_id)
                            ->disabled(fn (): bool => Auth::user()?->isAdmin() ?? false)
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('nomor_lp')
                            ->label('Nomor LP')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lp')
                            ->label('Tanggal LP')
                            ->required(),
                        Forms\Components\Select::make('perkara_id')
                            ->label('Perkara')
                            ->relationship('perkara', 'nama')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('dokumen_status')
                            ->label('Dokumen / Giat')
                            ->options(DokumenStatus::options())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('penyelesaian_id')
                            ->label('Penyelesaian')
                            ->relationship('penyelesaian', 'nama')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('petugas')
                            ->relationship('petugas', 'nama')
                            ->label('Petugas')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Data Korban')
                    ->schema([
                        Forms\Components\TextInput::make('nama_korban')
                            ->label('Nama Korban')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_lahir_korban')
                            ->label('Tempat Lahir Korban')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lahir_korban')
                            ->label('Tanggal Lahir Korban'),
                        Forms\Components\TextInput::make('hp_korban')
                            ->label('No HP Korban')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat_korban')
                            ->label('Alamat Korban')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('RTL')
                    ->schema([
                        Forms\Components\Repeater::make('rtls')
                            ->relationship()
                            ->label('Timeline RTL')
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal')
                                    ->required(),
                                Forms\Components\Textarea::make('keterangan')
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Data Kasus')
                    ->schema([
                        Infolists\Components\TextEntry::make('satker.nama')->label('Satker'),
                        Infolists\Components\TextEntry::make('nomor_lp')->label('Nomor LP'),
                        Infolists\Components\TextEntry::make('tanggal_lp')->date('d-m-Y')->label('Tanggal LP'),
                        Infolists\Components\TextEntry::make('perkara.nama')->label('Perkara'),
                        Infolists\Components\TextEntry::make('dokumen_status')
                            ->label('Dokumen/Giat')
                            ->formatStateUsing(fn ($state): string => strtoupper((string) ($state?->value ?? $state))),
                        Infolists\Components\TextEntry::make('penyelesaian.nama')
                            ->label('Penyelesaian')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('petugas_list')
                            ->label('Petugas')
                            ->state(fn (Kasus $record): string => $record->petugas->pluck('nama')->join(', ') ?: '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Data Korban')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_korban')->label('Nama Korban'),
                        Infolists\Components\TextEntry::make('tempat_lahir_korban')->label('Tempat Lahir Korban')->default('-'),
                        Infolists\Components\TextEntry::make('tanggal_lahir_korban')->date('d-m-Y')->label('Tanggal Lahir Korban')->default('-'),
                        Infolists\Components\TextEntry::make('hp_korban')->label('HP Korban')->default('-'),
                        Infolists\Components\TextEntry::make('alamat_korban')->label('Alamat')->columnSpanFull()->default('-'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Timeline RTL')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('rtls')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('tanggal')->date('d-m-Y')->label('Tanggal'),
                                Infolists\Components\TextEntry::make('keterangan')->label('Keterangan'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('nomor_lp')
                    ->label('Nomor LP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_lp')
                    ->label('Tgl LP')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_korban')
                    ->label('Korban')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perkara.nama')
                    ->label('Perkara')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latestRtl.keterangan')
                    ->label('RTL Terbaru')
                    ->default('-')
                    ->limit(40),
                Tables\Columns\TextColumn::make('dokumen_status')
                    ->label('Dokumen/Giat')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => strtoupper((string) ($state?->value ?? $state)))
                    ->color(fn ($state): string => ($state?->value ?? $state) === DokumenStatus::Sidik->value ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('petugas_list')
                    ->label('Petugas')
                    ->state(fn (Kasus $record): string => $record->petugas->pluck('nama')->join(', ') ?: '-')
                    ->limit(40),
                Tables\Columns\TextColumn::make('penyelesaian.nama')
                    ->label('Penyelesaian')
                    ->badge()
                    ->default('-'),
                Tables\Columns\TextColumn::make('satker.nama')
                    ->label('Satker')
                    ->toggleable(isToggledHiddenByDefault: Auth::user()?->isAdmin() ?? false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('satker_id')
                    ->label('Satker')
                    ->relationship('satker', 'nama')
                    ->visible(fn (): bool => Auth::user()?->isSuperAdmin() ?? false),
                Tables\Filters\SelectFilter::make('perkara_id')
                    ->label('Perkara')
                    ->relationship('perkara', 'nama'),
                Tables\Filters\SelectFilter::make('dokumen_status')
                    ->label('Dokumen/Giat')
                    ->options(DokumenStatus::options()),
                Tables\Filters\SelectFilter::make('penyelesaian_id')
                    ->label('Penyelesaian')
                    ->relationship('penyelesaian', 'nama'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RtlsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasuses::route('/'),
            'create' => Pages\CreateKasus::route('/create'),
            'view' => Pages\ViewKasus::route('/{record}'),
            'edit' => Pages\EditKasus::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'satker:id,nama',
                'perkara:id,nama',
                'penyelesaian:id,nama',
                'petugas:id,nama',
                'latestRtl',
                'rtls:id,kasus_id,tanggal,keterangan',
            ]);
    }
}
