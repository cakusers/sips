<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Forms\Get;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GrossProfitChart extends ApexChartWidget
{

    protected static ?string $chartId = 'grossProfitChart';
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        $period = '';
        $month = '';
        $year = '';

        $heading = 'Grafik Laba Kotor %s ';

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

    protected static ?string $footer = 'Menampilkan estimasi laba kotor dari transaksi yang telah selesai';

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
     * Mendapatkan data laba kotor Mingguan dalam bulan dan tahun tertentu
     * @param   int $month
     * @param   int $year
     * @return  Collection laba kotor per minggu
     */
    protected function getGrossProfitWeekly(int $month, int $year): Collection
    {
        $startDate = $month === Carbon::now()->month ? Carbon::now()->startOfMonth() : Carbon::create($year, $month)->startOfMonth();
        $endDate = $month === Carbon::now()->month ? Carbon::now() : Carbon::create($year, $month)->endOfMonth();
        $weeklyGrossProfit = Transaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '=', TransactionStatus::COMPLETE)
            ->selectRaw("
                DATE(created_at - INTERVAL WEEKDAY(created_at) DAY) as week_start_date,
                SUM(
                    CASE
                        WHEN type = ? THEN total_price
                        ELSE 0
                    END
                ) -
                SUM(
                    CASE
                        WHEN type = ? THEN total_price
                        ELSE 0
                    END
                ) as profit
            ", [TransactionType::SELL, TransactionType::PURCHASE])
            ->groupBy('week_start_date')
            ->pluck('profit', 'week_start_date');

        // Template tanggal
        $period = CarbonPeriod::create($startDate, '1 week', $endDate);
        $dateTemplate = collect();
        foreach ($period as $date) {
            $weekStartDate = $date->startOfWeek()->format('Y-m-d');
            $dateTemplate[$weekStartDate] = 0;
        }

        $result = $dateTemplate->merge($weeklyGrossProfit);
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
                // $data = $this->getPurchaseYearly();
                break;

            case 'monthly':
                $year = $year = $this->filterFormData['year'];
                // $data = $this->getPurchaseMonthly($year);
                break;

            default:
                $month = $this->filterFormData['month'];
                $year = $this->filterFormData['year'];
                $data = $this->getGrossProfitWeekly($month, $year);
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
                    'name' => 'Pembelian',
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
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'title' => [
                    'text' => 'Rupiah (Rp)'
                ]
            ],
            'colors' => ['#7008e7'],
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
