<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTicketsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::with(['category', 'creator', 'assignedTo'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.nom')
                    ->label('Catégorie'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créé par'),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigné à')
                    ->placeholder('Non assigné'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nouveau' => 'danger',
                        'en_cours' => 'warning',
                        'resolu' => 'success',
                        'cloture' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
