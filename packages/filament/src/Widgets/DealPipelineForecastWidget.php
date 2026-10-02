<?php

declare(strict_types=1);

namespace Odden\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Odden\Sales\Actions\CalculatePipelineForecastAction;

class DealPipelineForecastWidget extends StatsOverviewWidget
{
    public ?int $pipelineId = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $forecast = app(CalculatePipelineForecastAction::class)->execute($this->pipelineId);

        return [
            Stat::make('Open Pipeline', '$'.number_format($forecast['open_value'], 2))
                ->description("{$forecast['open_count']} active open deals")
                ->icon(Heroicon::CurrencyDollar)
                ->color('info'),

            Stat::make('Weighted Forecast', '$'.number_format($forecast['weighted_forecast'], 2))
                ->description('Probability-weighted revenue')
                ->icon(Heroicon::ChartBar)
                ->color('primary'),

            Stat::make('Closed Won', '$'.number_format($forecast['won_value'], 2))
                ->description("{$forecast['won_count']} deals closed won")
                ->icon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make('Win Rate', "{$forecast['win_rate']}%")
                ->description('Avg size: $'.number_format($forecast['average_deal_size'], 0))
                ->icon(Heroicon::ArrowTrendingUp)
                ->color($forecast['win_rate'] >= 50.0 ? 'success' : 'warning'),
        ];
    }
}
