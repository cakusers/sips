<?php

namespace App\Filament\Widgets\Decarbonization;

use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\CarbonPeriod;
use Filament\Support\RawJs;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Htmlable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DecarbonizationInChart extends ApexChartWidget
{
    protected static ?string $chartId = 'DecarbonizationInChart';
    protected int | string | array $columnSpan = 'full';
    protected function getHeading(): null|string|Htmlable|View
    {
        $period = '';
        $month = '';
        $year = '';

        $heading = 'Grafik Dekarbonisasi Masuk ';

        switch ($this->filterFormData['period']) {
            case 'weekly':
                $period = 'Mingguan';
                $filterMonth = $this->filterFormData['month'];
                $year = $this->filterFormData['year'];

                if (!$filterMonth || !$year) {
                    return $heading . '(Periode Invalid)';
                }

                $month = Carbon::create()->month((int) $filterMonth)
                    ->locale('id')
                    ->translatedFormat('M');

                $heading = sprintf($heading . '%s ( %s %s )', $period, $month, $year);
                break;

            case 'monthly':
                $period = 'Bulanan';
                $year = $this->filterFormData['year'];

                if (!$year) {
                    return $heading . '(Periode Invalid)';
                }

                $heading = sprintf($heading . '%s ( %s )', $period, $year);
                break;

            case 'yearly':
                $period = 'Tahunan';
                $heading = sprintf($heading . '%s', $period);
                break;

            default:
                $heading = $heading . '(Periode Invalid)';
                break;
        }

        return $heading;
    }

    /**
     * Fungsi Helper untuk mengambil tahun yang ada pada data stock movement
     * @return array [tahun => tahun]
     */
    protected static function getAvailableYear(): array
    {
        return StockMovement::query()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();
    }

    /**
     * Fungsi Helper untuk mengambil Bulan pada tahun tertentu yang ada pada data stock movement
     * @param   (int | string | null) $year
     * @return  array [nomerBulan => namaBulan]
     */
    protected static function getAvailableMonth(int|string|null $year): array
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
        // $fakeNow = Carbon::create(2025, 7, 30);
        // Carbon::setTestNow($fakeNow);
        // try {
        return [
            Select::make('period')
                ->label('Periode')
                ->options([
                    'weekly' => 'Mingguan',
                    'monthly' => 'Bulanan',
                    'yearly' => 'Tahunan'
                ])
                ->default('weekly')
                ->native(false)
                ->live(),
            /**
             * Tampilkan ketika period mingguan
             */
            Select::make('month')
                ->label('Bulan')
                ->options(fn(Get $get) => $this->getAvailableMonth($get('year')))
                ->default(Carbon::now()->month)
                ->visible(fn(Get $get) => $get('period') === 'weekly')
                ->native(false)
                ->live(),
            /**
             * Tampilkan ketika period mingguan dan bulanan
             */
            Select::make('year')
                ->label('Tahun')
                ->options($this->getAvailableYear())
                ->afterStateUpdated(fn(Set $set, $state) => !$state ? $set('month', null) : $state)
                ->default(Carbon::now()->year)
                ->hidden(fn(Get $get) => $get('period') === 'yearly')
                ->native(false)
                ->live(),
        ];
        // } finally {
        //     Carbon::setTestNow();
        // }
    }

    /**
     * Mendapatkan data Tahunan dari karbon yang masuk
     * @return Collection karbon masuk per tahun [tahun => karbon]
     */
    protected function getYearlyCarbonIn(): Collection
    {
        $yearlyCarbon = StockMovement::query()
            ->where('carbon_footprint_change_kg_co2e', '>', 0.0)
            ->selectRaw("YEAR(created_at) as year, SUM(carbon_footprint_change_kg_co2e) as carbon")
            ->groupBy('year')
            ->pluck('carbon', 'year');

        return $yearlyCarbon;
    }

    /**
     * Mendapatkan data Bulanan dari karbon yang masuk dalam tahun tertentu
     * @param   (int | string | null) $year
     * @return  Collection karbon masuk per bulan ['bulan, tahun' => karbon]
     */
    protected function getMonthlyCarbonIn(int|string|null $year): Collection
    {
        if (!$year) {
            return collect([
                'invalid' => 0
            ]);
        }

        $startDate = $year === Carbon::now()->year ? Carbon::now()->startOfYear() : Carbon::create($year)->startOfYear();
        $endDate = $year === Carbon::now()->year ? Carbon::now() : Carbon::create($year)->endOfYear();

        $monthlyCarbon = StockMovement::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('carbon_footprint_change_kg_co2e', '>', 0.0)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(carbon_footprint_change_kg_co2e) as carbon')
            ->groupBy('month')
            ->pluck('carbon', 'month');

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);
        $monthTemplate = collect();
        foreach ($period as $date) {
            $month = $date->startOfMonth()->format('Y-m');
            $monthTemplate[$month] = 0;
        }

        $result = $monthTemplate->merge($monthlyCarbon);
        $result = $result->mapWithKeys(function ($total, $date) {
            $formattedDate = Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('M Y');
            return [$formattedDate => $total];
        });

        return $result;
    }

    /**
     * Mendapatkan data Mingguan dari karbon yang masuk dalam bulan dan tahun tertentu
     * @param   (int | string | null) $month
     * @param   (int | string | null) $year
     * @return  Collection karbon masuk per minggu ['tgl awal minggu, bulan, tahun' => karbon]
     */
    protected function getWeeklyCarbonIn(int|string|null $month, int|string|null $year): Collection
    {
        if (!$month || !$year) {
            return collect([
                'invalid' => 0
            ]);
        }

        $startDate = $month === Carbon::now()->month ? Carbon::now()->startOfMonth()->startOfWeek(Carbon::MONDAY) : Carbon::create($year, $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDate = $month === Carbon::now()->month ? Carbon::now() : Carbon::create($year, $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // dd($dateTemplate->keys()[0]);

        $weeklyCarbon = StockMovement::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('carbon_footprint_change_kg_co2e', '>', 0.0)
            ->selectRaw("DATE(created_at - INTERVAL WEEKDAY(created_at) DAY) as week_start_date, SUM(carbon_footprint_change_kg_co2e) as carbon")
            ->groupBy('week_start_date')
            ->pluck('carbon', 'week_start_date');

        // Template tanggal
        $period = CarbonPeriod::create($startDate, '1 week', $endDate);
        $dateTemplate = collect();
        foreach ($period as $date) {
            $weekStartDate = $date->startOfWeek()->format('Y-m-d');
            $dateTemplate[$weekStartDate] = 0;
        }

        $result = $dateTemplate->merge($weeklyCarbon);
        $result = $result->mapWithKeys(function ($total, $date) {
            $formattedDate = Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('d M Y');
            return [$formattedDate => $total];
        });

        return $result;
    }

    /**
     * Mendapatkan data chart berdasarkan periode yang dipilih pada filter
     * @param ?string $period (tahunan, bulanan, mingguan)
     * @return Collection $data
     */
    protected function getChartData(?string $period): Collection
    {
        $data = collect();
        switch ($period) {
            case 'weekly':
                $month = $this->filterFormData['month'];
                $year = $this->filterFormData['year'];
                $data = $this->getWeeklyCarbonIn($month, $year);
                break;
            case 'monthly':
                $year = $this->filterFormData['year'];
                $data = $this->getMonthlyCarbonIn($year);
                break;

            case 'yearly':
                $data = $this->getYearlyCarbonIn();
                break;

            default:
                $data = collect([
                    'invalid' => 0
                ]);
                break;
        }

        return $data;
    }

    /**
     * Chart options (seri es, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // $fakeNow = Carbon::create(2025, 7, 30);
        // Carbon::setTestNow($fakeNow);
        // try {
        $period = $this->filterFormData['period'];
        $data = $this->getChartData($period);

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false]
            ],
            'series' => [
                [
                    'name' => 'Dekarbonisasi Masuk',
                    'data' => $data->values()->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $data->keys()->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'title' => [
                    'text' => 'Periode'
                ]
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'title' => [
                    'text' => 'Dekarbonisasi'
                ]
            ],
            'colors' => ['#9014eb'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
        ];
        // } finally {
        //     Carbon::setTestNow();
        // }
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val.toLocaleString('id-ID');
                    }
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
