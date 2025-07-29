<?php

namespace App\Filament\Widgets\Sales;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueChart extends ApexChartWidget
{
    protected static ?string $chartId = 'revenueChart';
    protected function getHeading(): null|string|Htmlable|View
    {
        $period = '';
        $month = '';
        $year = '';

        $heading = 'Grafik Pendapatan %s ';

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
    protected static ?string $footer = 'Menampilkan pendapatan (penjualan) dari transaksi yang telah selesai';

    /**
     * Fungsi Helper untuk mengambil tahun yang ada pada data transaksi
     * @return Collection tahun
     */
    protected static function getAvailableYear(): Collection
    {
        return Transaction::query()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year');
    }

    /**
     * Fungsi Helper untuk mengambil Bulan pada tahun tertentu yang ada pada data transaksi
     * @param   int $year
     * @return  Collection bulan
     */
    protected static function getAvailableMonth(int $year): Collection
    {
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
     * Mendapatkan data Pendapatan Tahunan
     * @return Collection pendapatan per tahun
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
     * @param   int $year
     * @return  Collection pendapatan per bulan
     */
    protected function getRevenueMonthly(int $year): Collection
    {
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
     * @param   int $month
     * @param   int $year
     * @return  Collection pendapatan per minggu
     */
    protected function getRevenueWeekly(int $month, int $year): Collection
    {
        $startDate = $month === Carbon::now()->month ? Carbon::now()->startOfMonth() : Carbon::create($year, $month)->startOfMonth();
        $endDate = $month === Carbon::now()->month ? Carbon::now() : Carbon::create($year, $month)->endOfMonth();

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

    protected function getChartData(string $period): Collection
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

            default:
                $month = $this->filterFormData['month'];
                $year = $this->filterFormData['year'];
                $data = $this->getRevenueWeekly($month, $year);
                break;
        }

        return $data;
    }

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
            'colors' => ['#7e50cc'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
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
                        return val.toLocaleString('id-ID');
                    }
                }
            }
        }
        JS);
    }
}
