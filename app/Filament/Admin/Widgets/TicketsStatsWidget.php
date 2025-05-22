<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tickets en attente', Ticket::where('statut', 'nouveau')->count())
                ->description('Tickets nécessitant une validation')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
            Stat::make('Tickets en cours', Ticket::where('statut', 'en_cours')->count())
                ->description('Tickets en cours de traitement')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make('Tickets résolus', Ticket::where('statut', 'resolu')->orWhere('statut', 'cloture')->count())
                ->description('Tickets résolus ou clôturés')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
