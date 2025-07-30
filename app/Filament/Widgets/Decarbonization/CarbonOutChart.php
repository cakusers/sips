<?php

namespace App\Filament\Widgets\Decarbonization;

use App\Enums\MovementType;
use Carbon\Carbon;
use Filament\Forms\Get;
use Carbon\CarbonPeriod;
use Filament\Support\RawJs;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Htmlable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CarbonOutChart extends ApexChartWidget
{

    protected static ?string $chartId = 'carbonOutChart';
    protected int | string | array $columnSpan = 'full';
    protected function getHeading(): null|string|Htmlable|View
    {
        $period = '';
        $month = '';
        $year = '';

        $heading = 'Grafik Karbon Sampah Keluar %s ';

        switch ($this->filterFormData['period']) {
            case 'weekly':
                $period = 'Mingguan';
                $filterMonth = (int) $this->filterFormData['month'];
                $month = Carbon::create()->month($filterMonth)
                    ->locale('id')
                    ->translatedFormat('M');
                $year = $this->filterFormData['year'];
                $heading = sprintf($heading . '( %s %s )', $period, $month, $year);
                break;

            case 'monthly':
                $period = 'Bulanan';
                $year = $this->filterFormData['year'];
                $heading = sprintf($heading . '( %s )', $period, $year);
                break;

            default:
                $period = 'Tahunan';
                $heading = sprintf($heading, $period);
                break;
        }

        return $heading;
    }

    /**
     * Fungsi Helper untuk mengambil tahun yang ada pada data stock movement
     * @return Collection tahun
     */
    protected static function getAvailableYear(): Collection
    {
        return StockMovement::query()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year');
    }

    /**
     * Fungsi Helper untuk mengambil Bulan pada tahun tertentu yang ada pada data stock movement
     * @param   int $year
     * @return  Collection bulan
     */
    protected static function getAvailableMonth(int $year): Collection
    {
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

        return $months;
    }

    /**
     * Filter
     */
    protected function getFormSchema(): array
    {
        return [
            Select::make('period')
                ->label('Periode')
                ->options([
                    'weekly' => 'Mingguan',
                    'monthly' => 'Bulanan',
                    'yearly' => 'Tahunan'
                ])
                ->default('weekly')
                ->live(),
            /**
             * Tampilkan ketika period mingguan
             */
            Select::make('month')
                ->label('Bulan')
                ->options(fn(Get $get) => $this->getAvailableMonth($get('year')))
                ->default(Carbon::now()->month)
                ->visible(fn(Get $get) => $get('period') === 'weekly')
                ->live(),
            /**
             * Tampilkan ketika period mingguan dan bulanan
             */
            Select::make('year')
                ->label('Tahun')
                ->options($this->getAvailableYear())
                ->default(Carbon::now()->year)
                ->hidden(fn(Get $get) => $get('period') === 'yearly')
                ->live(),
        ];
    }

    /**
     * Mendapatkan data Tahunan dari karbon yang keluar
     * @return Collection pendapatan per tahun
     */
    protected function getYearlyCarbonOut(): Collection
    {
        $yearlyCarbon = StockMovement::query()
            ->where('type', MovementType::SELLOUT)
            ->selectRaw("YEAR(created_at) as year, ABS(SUM(carbon_footprint_change_kg_co2e)) as carbon")
            ->groupBy('year')
            ->pluck('carbon', 'year');

        return $yearlyCarbon;
    }

    /**
     * Mendapatkan data Bulanan dari karbon yang keluar dalam tahun tertentu
     * @param   int $year
     * @return  Collection pembelian per bulan
     */
    protected function getMonthlyCarbonOut(int $year): Collection
    {
        $startDate = $year === Carbon::now()->year ? Carbon::now()->startOfYear() : Carbon::create($year)->startOfYear();
        $endDate = $year === Carbon::now()->year ? Carbon::now() : Carbon::create($year)->endOfYear();

        $monthlyCarbon = StockMovement::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('type', [MovementType::MANUALOUT, MovementType::SELLOUT, MovementType::RETURNEDOUT])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, ABS(SUM(carbon_footprint_change_kg_co2e)) as carbon')
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
     * Mendapatkan data Mingguan dari karbon yang keluar dalam bulan dan tahun tertentu
     * @param   int $month
     * @param   int $year
     * @return  Collection pendapatan per minggu
     */
    protected function getWeeklyCarbonOut(int $month, int $year): Collection
    {
        $startDate = $month === Carbon::now()->month ? Carbon::now()->startOfMonth()->startOfWeek(Carbon::MONDAY) : Carbon::create($year, $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDate = $month === Carbon::now()->month ? Carbon::now() : Carbon::create($year, $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // dd($dateTemplate->keys()[0]);

        $weeklyCarbon = StockMovement::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('type', [MovementType::MANUALOUT, MovementType::SELLOUT, MovementType::RETURNEDOUT])
            ->selectRaw("DATE(created_at - INTERVAL WEEKDAY(created_at) DAY) as week_start_date, ABS(SUM(carbon_footprint_change_kg_co2e)) as carbon")
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
     * @param string $period (tahunan, bulanan, mingguan)
     * @return Collection $data
     */
    protected function getChartData(string $period): Collection
    {
        $data = collect();
        switch ($period) {
            case 'yearly':
                $data = $this->getYearlyCarbonOut();
                break;

            case 'monthly':
                $year = $year = $this->filterFormData['year'];
                $data = $this->getMonthlyCarbonOut($year);
                break;

            default:
                $month = $this->filterFormData['month'];
                $year = $this->filterFormData['year'];
                $data = $this->getWeeklyCarbonOut($month, $year);
                break;
        }

        return $data;
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
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
                    'name' => 'Karbon Keluar',
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
                    'text' => 'Jejak Karbon'
                ]
            ],
            'colors' => ['#f97316'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            // 'plotOptions' => [
            //     'pie' => [
            //         'donut' => [
            //             'labels' => [
            //                 'show' => true
            //             ]
            //         ]
            //     ]
            // ]
        ];
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
