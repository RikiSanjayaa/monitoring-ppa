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
                            ->label('Jenis Kasus')
                            ->relationship('perkara', 'nama')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('tindak_pidana_pasal')
                            ->label('Tindak Pidana/Pasal')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hubungan_pelaku_dengan_korban')
                            ->label('Hubungan Tersangka dengan Korban')
                            ->maxLength(255),
                        Forms\Components\Select::make('dokumen_status')
                            ->label('Dokumen / Giat')
                            ->options(DokumenStatus::options())
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('proses_pidana')
                            ->label('Proses Pidana')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('kronologi_kejadian')
                            ->label('Kronologi Kejadian')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('kronologi_kejadian_file')
                            ->label('Lampiran Kronologi Kejadian')
                            ->disk('public')
                            ->directory('kasus/kronologi')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/*',
                            ])
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('laporan_polisi')
                            ->label('Laporan Polisi')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('laporan_polisi_file')
                            ->label('Lampiran Laporan Polisi')
                            ->disk('public')
                            ->directory('kasus/laporan-polisi')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/*',
                            ])
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
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
                Forms\Components\Section::make('Identitas')
                    ->schema([
                        Forms\Components\Fieldset::make('Korban')
                            ->schema([
                                Forms\Components\Repeater::make('korbans')
                                    ->relationship()
                                    ->label('Daftar Korban')
                                    ->defaultItems(1)
                                    ->schema([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama Korban')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->maxLength(255),
                                        Forms\Components\DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\TextInput::make('hp')
                                            ->label('No HP')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('alamat')
                                            ->label('Alamat')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Forms\Components\Fieldset::make('Tersangka')
                            ->schema([
                                Forms\Components\Repeater::make('tersangkas')
                                    ->relationship('tersangkas')
                                    ->label('Daftar Tersangka')
                                    ->defaultItems(1)
                                    ->schema([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama Tersangka')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->maxLength(255),
                                        Forms\Components\DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\TextInput::make('hp')
                                            ->label('No HP')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('alamat')
                                            ->label('Alamat')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Forms\Components\Fieldset::make('Saksi')
                            ->schema([
                                Forms\Components\Repeater::make('saksis')
                                    ->relationship()
                                    ->label('Daftar Saksi')
                                    ->defaultItems(0)
                                    ->schema([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama Saksi')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->maxLength(255),
                                        Forms\Components\DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir'),
                                        Forms\Components\TextInput::make('hp')
                                            ->label('No HP')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('alamat')
                                            ->label('Alamat')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
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
                        Infolists\Components\TextEntry::make('perkara.nama')->label('Jenis Kasus'),
                        Infolists\Components\TextEntry::make('tindak_pidana_pasal')
                            ->label('Tindak Pidana/Pasal')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('hubungan_pelaku_dengan_korban')
                            ->label('Hubungan Tersangka dengan Korban')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('dokumen_status')
                            ->label('Dokumen/Giat')
                            ->formatStateUsing(fn ($state): string => strtoupper((string) ($state?->value ?? $state))),
                        Infolists\Components\TextEntry::make('proses_pidana')
                            ->label('Proses Pidana')
                            ->default('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('kronologi_kejadian')
                            ->label('Kronologi Kejadian')
                            ->default('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('laporan_polisi')
                            ->label('Laporan Polisi')
                            ->default('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('penyelesaian.nama')
                            ->label('Penyelesaian')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('petugas_list')
                            ->label('Petugas')
                            ->state(fn (Kasus $record): string => $record->petugas->pluck('nama')->join(', ') ?: '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Lampiran')
                    ->schema([
                        Infolists\Components\TextEntry::make('kronologi_kejadian_file')
                            ->label('Lampiran Kronologi Kejadian')
                            ->formatStateUsing(fn (?string $state): string => static::attachmentPreviewHtml($state))
                            ->html()
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('laporan_polisi_file')
                            ->label('Lampiran Laporan Polisi')
                            ->formatStateUsing(fn (?string $state): string => static::attachmentPreviewHtml($state))
                            ->html()
                            ->columnSpanFull(),
                    ]),
                Infolists\Components\Section::make('Identitas')
                    ->schema([
                        Infolists\Components\Fieldset::make('Korban')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('korbans')
                                    ->label('Daftar Korban')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama')->label('Nama Korban'),
                                        Infolists\Components\TextEntry::make('tempat_lahir')->label('Tempat Lahir')->default('-'),
                                        Infolists\Components\TextEntry::make('tanggal_lahir')->date('d-m-Y')->label('Tanggal Lahir')->default('-'),
                                        Infolists\Components\TextEntry::make('hp')->label('No HP')->default('-'),
                                        Infolists\Components\TextEntry::make('alamat')->label('Alamat')->columnSpanFull()->default('-'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Infolists\Components\Fieldset::make('Tersangka')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('tersangkas')
                                    ->label('Daftar Tersangka')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama')->label('Nama Tersangka'),
                                        Infolists\Components\TextEntry::make('tempat_lahir')->label('Tempat Lahir')->default('-'),
                                        Infolists\Components\TextEntry::make('tanggal_lahir')->date('d-m-Y')->label('Tanggal Lahir')->default('-'),
                                        Infolists\Components\TextEntry::make('hp')->label('No HP')->default('-'),
                                        Infolists\Components\TextEntry::make('alamat')->label('Alamat')->columnSpanFull()->default('-'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Infolists\Components\Fieldset::make('Saksi')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('saksis')
                                    ->label('Daftar Saksi')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama')->label('Nama Saksi'),
                                        Infolists\Components\TextEntry::make('tempat_lahir')->label('Tempat Lahir')->default('-'),
                                        Infolists\Components\TextEntry::make('tanggal_lahir')->date('d-m-Y')->label('Tanggal Lahir')->default('-'),
                                        Infolists\Components\TextEntry::make('hp')->label('No HP')->default('-'),
                                        Infolists\Components\TextEntry::make('alamat')->label('Alamat')->columnSpanFull()->default('-'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('korban_list')
                    ->label('Korban')
                    ->state(fn (Kasus $record): string => $record->korbanList())
                    ->limit(30),
                Tables\Columns\TextColumn::make('tersangka_list')
                    ->label('Tersangka')
                    ->state(fn (Kasus $record): string => $record->tersangkaList())
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('saksi_list')
                    ->label('Saksi')
                    ->state(fn (Kasus $record): string => $record->saksis->pluck('nama')->join(', ') ?: '-')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('perkara.nama')
                    ->label('Jenis Kasus')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tindak_pidana_pasal')
                    ->label('Tindak Pidana/Pasal')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('hubungan_pelaku_dengan_korban')
                    ->label('Hub. Tersangka-Korban')
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
                    ->label('Jenis Kasus')
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
                'korbans:id,kasus_id,nama,tempat_lahir,tanggal_lahir,alamat,hp',
                'tersangkas:id,kasus_id,nama,tempat_lahir,tanggal_lahir,alamat,hp',
                'saksis:id,kasus_id,nama,tempat_lahir,tanggal_lahir,alamat,hp',
                'latestRtl',
                'rtls:id,kasus_id,tanggal,keterangan',
            ]);
    }

    private static function attachmentPreviewHtml(?string $path): string
    {
        if (! $path) {
            return '<span style="color:#9ca3af;">Belum ada lampiran.</span>';
        }

        $url = '/storage/'.$path;
        $fileName = basename($path);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);

        $previewHtml = $isImage
            ? sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer"><img src="%s" alt="%s" style="max-width:420px;max-height:280px;border-radius:10px;border:1px solid #d1d5db;object-fit:cover;"></a>',
                e($url),
                e($url),
                e($fileName),
            )
            : sprintf(
                '<div style="display:inline-block;padding:10px 12px;border:1px dashed #9ca3af;border-radius:10px;color:#6b7280;">Preview tidak tersedia untuk .%s</div>',
                e($extension !== '' ? $extension : 'file'),
            );

        return sprintf(
            '<div style="display:flex;flex-direction:column;gap:8px;"><div>%s</div><div style="font-weight:600;">%s</div><div style="display:flex;gap:8px;"><a href="%s" target="_blank" rel="noopener noreferrer" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;">Buka</a><a href="%s" download style="padding:6px 10px;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;">Unduh</a></div></div>',
            $previewHtml,
            e($fileName),
            e($url),
            e($url),
        );
    }
}
