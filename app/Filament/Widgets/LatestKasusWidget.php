<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\KasusResource;
use App\Models\Kasus;
use App\Support\KasusDashboardFilters;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestKasusWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Kasus Terbaru';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KasusDashboardFilters::apply(Kasus::query(), $this->filters ?? [])
                    ->with(['satker:id,nama', 'perkara:id,nama'])
                    ->latest('tanggal_lp')
                    ->latest('id')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor_lp')
                    ->label('Nomor LP')
                    ->copyable()
                    ->copyMessage('Nomor LP disalin')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-o-clipboard-document')
                    ->iconColor('gray')
                    ->iconPosition(IconPosition::After)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lp')
                    ->label('Tanggal LP')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('satker.nama')
                    ->label('Satker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perkara.nama')
                    ->label('Jenis Kasus')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dokumen_status')
                    ->label('Dokumen/Giat')
                    ->badge()
                    ->formatStateUsing(fn($state): string => strtoupper((string) ($state?->value ?? $state))),
            ])
            ->recordUrl(fn(Kasus $record): string => KasusResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->defaultSort('tanggal_lp', 'desc');
    }
}
