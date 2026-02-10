<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SatkerResource\Pages;
use App\Models\Satker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SatkerResource extends Resource
{
    protected static ?string $model = Satker::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'satker';

    protected static ?string $modelLabel = 'Satker';

    protected static ?string $pluralModelLabel = 'Satker';

    public static function canAccess(): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'subdit' => 'Subdit',
                        'polres' => 'Polres',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('kode')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
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
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah User')
                    ->counts('users'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'subdit' => 'Subdit',
                        'polres' => 'Polres',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSatkers::route('/'),
            'create' => Pages\CreateSatker::route('/create'),
            'view' => Pages\ViewSatker::route('/{record}'),
            'edit' => Pages\EditSatker::route('/{record}/edit'),
        ];
    }
}
