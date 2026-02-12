<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanLaporanResource\Pages;
use App\Models\PengaturanLaporan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PengaturanLaporanResource extends Resource
{
    protected static ?string $model = PengaturanLaporan::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 90;

    protected static ?string $modelLabel = 'Pengaturan Laporan';

    protected static ?string $pluralModelLabel = 'Pengaturan Laporan';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->isSuperAdmin() || $user?->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Target Satker')
                    ->schema([
                        Forms\Components\Placeholder::make('scope_info')
                            ->label('Ruang Lingkup')
                            ->content(function (): string {
                                $user = Auth::user();

                                if ($user?->isSuperAdmin()) {
                                    return 'Berlaku untuk export Super Admin (lintas satker).';
                                }

                                return 'Berlaku untuk export satker Anda: '.($user?->satker?->nama ?? '-');
                            }),
                    ]),
                Forms\Components\Section::make('Kop dan Judul Laporan')
                    ->schema([
                        Forms\Components\TextInput::make('kop_baris_1')->required()->maxLength(255),
                        Forms\Components\TextInput::make('kop_baris_2')->required()->maxLength(255),
                        Forms\Components\TextInput::make('kop_baris_3')->required()->maxLength(255),
                        Forms\Components\Placeholder::make('judul_otomatis_info')
                            ->label('Judul Laporan')
                            ->content('Judul export dibuat otomatis dari jenis kasus, bulan/tahun data, dan satker.'),
                        Forms\Components\Hidden::make('judul_utama')
                            ->default('AUTO_TITLE'),
                        Forms\Components\Hidden::make('judul_rekap')
                            ->default('AUTO_RECAP_TITLE'),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Tanda Tangan')
                    ->schema([
                        Forms\Components\TextInput::make('ttd_baris_1')->required()->maxLength(255),
                        Forms\Components\TextInput::make('ttd_baris_2')->required()->maxLength(255),
                        Forms\Components\TextInput::make('ttd_nama')->required()->maxLength(255),
                        Forms\Components\TextInput::make('ttd_pangkat_nrp')->required()->maxLength(255),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('satker.nama')
                    ->label('Satker')
                    ->state(fn (PengaturanLaporan $record): string => $record->satker?->nama ?? 'Semua Satker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('judul_utama')
                    ->label('Judul')
                    ->state('Otomatis (berdasarkan data)')
                    ->badge(),
                Tables\Columns\TextColumn::make('ttd_nama')
                    ->label('Penanda Tangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengaturanLaporans::route('/'),
            'edit' => Pages\EditPengaturanLaporan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('satker:id,nama');

        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('user_id', $user->id);
    }
}
