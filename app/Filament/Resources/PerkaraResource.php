<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerkaraResource\Pages;
use App\Models\Perkara;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PerkaraResource extends Resource
{
    protected static ?string $model = Perkara::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'perkara';

    protected static ?string $modelLabel = 'Jenis Kasus';

    protected static ?string $pluralModelLabel = 'Jenis Kasus';

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
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
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
            'index' => Pages\ListPerkaras::route('/'),
            'create' => Pages\CreatePerkara::route('/create'),
            'view' => Pages\ViewPerkara::route('/{record}'),
            'edit' => Pages\EditPerkara::route('/{record}/edit'),
        ];
    }
}
