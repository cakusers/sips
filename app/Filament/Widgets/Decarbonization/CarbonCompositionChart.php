<?php

namespace App\Filament\Widgets\Decarbonization;

use Carbon\Carbon;
use Filament\Support\RawJs;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CarbonCompositionChart extends ApexChartWidget
{

    protected static ?string $chartId = 'carbonComposition';
    protected static ?string $heading = 'Karbon Terbesar Stok Saat Ini';

    protected function getCarbonByCategory(): Collection
    {
        return StockMovement::query()
            ->join('wastes', 'stock_movements.waste_id', '=', 'wastes.id')
            ->join('waste_categories', 'wastes.waste_category_id', '=', 'waste_categories.id')
            ->select(
                'waste_categories.name as category_name',
                DB::raw('SUM(stock_movements.carbon_footprint_change_kg_co2e) as total_carbon')
            )
            ->groupBy('category_name')
            ->orderByDesc('total_carbon')
            ->pluck('total_carbon', 'category_name')
        ;
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $fakeNow = Carbon::create(2025, 7, 30);
        Carbon::setTestNow($fakeNow);
        try {
            $data = $this->getCarbonByCategory();
            // dd($data);
            return [
                'chart' => [
                    'type' => 'donut',
                    'height' => 300,
                ],
                'series' => $data->values(),
                'labels' => $data->keys(),
                'legend' => [
                    'labels' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'plotOptions' => [
                    'pie' => [
                        'donut' =>  [
                            'size' => '35%'
                        ]
                    ]

                ]
            ];
        } finally {
            Carbon::setTestNow();
        }
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            dataLabels: {
                formatter: function (val) {
                    return (val/100).toLocaleString('id-ID', {style: 'percent', minimumFractionDigits:2})
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        const value = val.toLocaleString('id-ID');
                        return `<div>${value} Kg CO<sub>2</sub>e</div>`;
                    }
                }
            }
        }
        JS);
    }
}
