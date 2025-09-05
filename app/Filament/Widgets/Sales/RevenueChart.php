<?php

namespace App\Filament\Widgets\Sales;

use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Htmlable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueChart extends ApexChartWidget
{
    protected static ?string $chartId = 'revenueChart';
    protected function getHeading(): null|string|Htmlable|View
    {
        $period = '';
        $month = '';
        $year = '';

        $heading = 'Grafik Pendapatan ';

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
    protected static ?string $footer = 'Menampilkan pendapatan (penjualan) dari transaksi yang telah selesai';

    /**
     * Fungsi Helper untuk mengambil tahun yang ada pada data stock movement
     * @return array [tahun => tahun]
     */
    protected static function getAvailableYear(): array
    {
        return Transaction::query()
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

        $months = Transaction::query()
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
     * Mendapatkan data Pendapatan Tahunan
     * @return Collection pendapatan per tahun [tahun => pendapatan]
     */
    protected function getRevenueYearly(): Collection
    {
        $yearlyRevenue = Transaction::query()
            ->where('type', '=', TransactionType::SELL)
            ->where('status', '=', TransactionStatus::COMPLETE)
            ->selectRaw("YEAR(created_at) as year, SUM(total_price) as total")
            ->groupBy('year')
            ->pluck('total', 'year');

        return $yearlyRevenue;
    }

    /**
     * Mendapatkan data Pendapatan Bulanan dalam tahun tertentu
     * @param   (int | string | null) $year
     * @return  Collection pendapatan per bulan ['bulan, tahun' => pendapatan]
     */
    protected function getRevenueMonthly(int|string|null $year): Collection
    {
        if (!$year) {
            return collect([
                'invalid' => 0
            ]);
        }

        $startDate = $year === Carbon::now()->year ? Carbon::now()->startOfYear() : Carbon::create($year)->startOfYear();
        $endDate = $year === Carbon::now()->year ? Carbon::now() : Carbon::create($year)->endOfYear();
        $monthlyRevenue = Transaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('type', '=', TransactionType::SELL)
            ->where('status', '=', TransactionStatus::COMPLETE)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, SUM(total_price) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);
        $monthTemplate = collect();
        foreach ($period as $date) {
            $month = $date->startOfMonth()->format('Y-m');
            $monthTemplate[$month] = 0;
        }

        $result = $monthTemplate->merge($monthlyRevenue);
        $result = $result->mapWithKeys(function ($total, $date) {
            $formattedDate = Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('M Y');
            return [$formattedDate => $total];
        });

        return $result;
    }

    /**
     * Mendapatkan data Pendapatan Mingguan dalam bulan dan tahun tertentu
     * @param   (int | string | null) $month
     * @param   (int | string | null) $year
     * @return  Collection pendapatan per minggu ['tgl awal minggu, bulan, tahun' => pendapatan]
     */
    protected function getRevenueWeekly(int|string|null $month, int|string|null $year): Collection
    {
        if (!$month || !$year) {
            return collect([
                'invalid' => 0
            ]);
        }

        $startDate = $month === Carbon::now()->month ? Carbon::now()->startOfMonth()->startOfWeek(Carbon::MONDAY) : Carbon::create($year, $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDate = $month === Carbon::now()->month ? Carbon::now() : Carbon::create($year, $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeklyRevenue = Transaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('type', '=', TransactionType::SELL)
            ->where('status', '=', TransactionStatus::COMPLETE)
            ->selectRaw("DATE(created_at - INTERVAL (WEEKDAY(created_at)) DAY) as week_start_date, SUM(total_price) as total")
            ->groupBy('week_start_date')
            ->pluck('total', 'week_start_date');

        // Template tanggal
        $period = CarbonPeriod::create($startDate, '1 week', $endDate);
        $dateTemplate = collect();
        foreach ($period as $date) {
            $weekStartDate = $date->startOfWeek()->format('Y-m-d');
            $dateTemplate[$weekStartDate] = 0;
        }

        $result = $dateTemplate->merge($weeklyRevenue);
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
            case 'yearly':
                $data = $this->getRevenueYearly();
                break;

            case 'monthly':
                $year = $year = $this->filterFormData['year'];
                $data = $this->getRevenueMonthly($year);
                break;

            case 'weekly':
                $month = $this->filterFormData['month'];
                $year = $this->filterFormData['year'];
                $data = $this->getRevenueWeekly($month, $year);
                break;

            default:
                $data = collect([
                    'invalid' => 0
                ]);
                break;
        }

        return $data;
    }

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
                    'name' => 'Pendapatan',
                    'data' => $data->values()->all(),
                ],
            ],
            'xaxis' => [
                'categories' => $data->keys()->all(),
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
                    'text' => 'Pendapatan ( Rp )'
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
                        return val.toLocaleString('id-ID');
                    }
                }
            }
        }
        JS);
    }
}
