<?php

namespace App\Filament\Widgets\Decarbonization;

use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Support\RawJs;
use App\Models\StockMovement;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Htmlable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DecarbonizationInCompositionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'DecarbonizationInCompositionChart';
    protected function getHeading(): null|string|Htmlable|View
    {
        $filterMonth = $this->filterFormData['month'];
        $year = $this->filterFormData['year'] ? $this->filterFormData['year'] : '';
        $allTimeIn = $this->filterFormData['allTimeIn'];

        if (!$filterMonth) {
            $monthName = '';
        } else {
            $monthName = Carbon::create()->month((int) $filterMonth)
                ->locale('id')
                ->translatedFormat('M');
        }

        if ($allTimeIn) {
            return 'Komposisi Dekarbonisasi Masuk Sepanjang Waktu';
        }

        if (!$monthName || !$year) {
            return 'Komposisi Dekarbonisasi Masuk (Periode Invalid)';
        }

        return sprintf('Komposisi Dekarboinsasi Masuk %s %s', $monthName, $year);
    }

    /**
     * Fungsi Helper untuk mengambil tahun yang ada pada data stock movement
     * @return array [tahun => tahun]
     */
    protected static function getAvailableYear(): array
    {
        return StockMovement::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year', 'year')
            ->toArray();
    }

    /**
     * Fungsi Helper untuk mengambil Bulan pada tahun tertentu yang ada pada data stock movement
     * @param   int $year
     * @return  array [nomerBulan => namaBulan]
     */
    protected static function getAvailableMonth(int | string | null $year): array
    {
        if (!$year) {
            return [];
        }

        $months = StockMovement::query()
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month_number')
            ->distinct()
            ->orderBy('month_number', 'asc')
            ->pluck('month_number', 'month_number');

        $months = $months->map(
            fn($monthNumber) =>
            Carbon::create()
                ->month($monthNumber)
                ->locale('id')
                ->translatedFormat('F')
        );

        return $months->toArray();
    }

    /**
     * Filter
     */
    protected function getFormSchema(): array
    {
        $fakeNow = Carbon::create(2025, 7, 30);
        Carbon::setTestNow($fakeNow);
        try {
            return [
                Checkbox::make('allTimeIn')
                    ->label('Sepanjang Waktu')
                    ->live(),
                Select::make('month')
                    ->label('Bulan')
                    ->options(fn(Get $get) => $this->getAvailableMonth($get('year')))
                    ->default(Carbon::now()->month)
                    ->hidden(fn(Get $get) => $get('allTimeIn'))
                    ->native(false)
                    ->live(),
                Select::make('year')
                    ->label('Tahun')
                    ->options($this->getAvailableYear())
                    ->default(Carbon::now()->year)
                    ->hidden(fn(Get $get) => $get('allTimeIn'))
                    ->native(false)
                    ->live(),
            ];
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Mendapatkan data chart berdasarkan bulan, tahun, atau sepanjang waktu
     * @param (int | string | null) $month
     * @param  (int | string | null) $year
     * @param ?bool $allTimeIn untuk mengambil seluruh komposisi sampah dari awal waktu hingga sekarang
     * @return Collection data
     */
    protected function getDecarbonizationIn(int|string|null $month, int|string|null $year, ?bool $allTimeIn = false): Collection
    {
        if (!$month) {
            return collect([
                'Tahun atau Bulan Tidak Valid' => 0
            ]);
        }

        if (!$year) {
            return collect([
                'Tahun atau Bulan Tidak Valid' => 0
            ]);
        }

        if ($allTimeIn) {
            return StockMovement::query()
                ->join('wastes', 'stock_movements.waste_id', '=', 'wastes.id')
                ->join('waste_categories', 'wastes.waste_category_id', '=', 'waste_categories.id')
                ->where('carbon_footprint_change_kg_co2e', '>', 0.0)
                ->select(
                    'waste_categories.name as category_name',
                    DB::raw('ABS(SUM(stock_movements.carbon_footprint_change_kg_co2e)) as total_carbon')
                )
                ->groupBy('category_name')
                ->orderByDesc('total_carbon')
                ->pluck('total_carbon', 'category_name');
        }

        return StockMovement::query()
            ->join('wastes', 'stock_movements.waste_id', '=', 'wastes.id')
            ->join('waste_categories', 'wastes.waste_category_id', '=', 'waste_categories.id')
            ->where('carbon_footprint_change_kg_co2e', '>', 0.0)
            ->whereMonth('stock_movements.created_at', $month)
            ->whereYear('stock_movements.created_at', $year)
            ->select(
                'waste_categories.name as category_name',
                DB::raw('ABS(SUM(stock_movements.carbon_footprint_change_kg_co2e)) as total_carbon')
            )
            ->groupBy('category_name')
            ->orderByDesc('total_carbon')
            ->pluck('total_carbon', 'category_name');
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
            // dd($this->getAvailableMonth(2025));
            $month = $this->filterFormData['month'];
            $year = $this->filterFormData['year'];
            $allTimeIn = $this->filterFormData['allTimeIn'];

            $data = $this->getDecarbonizationIn($month, $year, $allTimeIn);

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
