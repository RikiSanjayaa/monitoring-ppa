<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetugasResource\Pages;
use App\Models\Petugas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PetugasResource extends Resource
{
    protected static ?string $model = Petugas::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'petugas';

    protected static ?string $modelLabel = 'Petugas';

    protected static ?string $pluralModelLabel = 'Petugas';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->isSuperAdmin() || $user?->isAdmin() || $user?->isAtasan();
    }

    public static function form(Form $form): Form
    {
        return $form
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
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nrp')
                    ->label('NRP')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pangkat')
                    ->label('Pangkat')
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No HP')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nrp')
                    ->label('NRP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pangkat')
                    ->label('Pangkat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No HP'),
                Tables\Columns\TextColumn::make('satker.nama')
                    ->label('Satker')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('satker_id')
                    ->label('Satker')
                    ->relationship('satker', 'nama')
                    ->visible(fn (): bool => Auth::user()?->isSuperAdmin() ?? false),
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
            ])
            ->searchPlaceholder('Cari berdasarkan nama/NRP/pangkat/satker');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPetugas::route('/'),
            'create' => Pages\CreatePetugas::route('/create'),
            'view' => Pages\ViewPetugas::route('/{record}'),
            'edit' => Pages\EditPetugas::route('/{record}/edit'),
        ];
    }
}
