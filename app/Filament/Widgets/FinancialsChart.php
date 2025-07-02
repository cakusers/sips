<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FinancialsChart extends ApexChartWidget
{

    protected static ?string $chartId = 'financialsChart';

    public function getHeading(): string
    {
        $chartLabel = '';
        if ($this->filter === 'revenue') {
            $chartLabel = 'Pendapatan';
        } else {
            $chartLabel = 'Pembelian';
        }

        if ($this->filterFormData['timeFilter'] === 'weekly') {
            $chartLabel = $chartLabel . ' Mingguan';
        } else {
            $chartLabel = $chartLabel . ' Bulanan';
        }

        return "Grafik {$chartLabel}";
    }

    protected function getFooter(): ?string
    {
        $revenueFooter = 'Menampilkan pendapatan (penjualan) yang telah selesai dari transaksi yang telah selesai baik lunas maupun belum lunas.';
        $purchaseFooter = 'Menampilkan pembelian sampah yang telah selesai dari transaksi yang telah selesai baik lunas maupun belum lunas.';
        return $this->filter === 'revenue' ? $revenueFooter : $purchaseFooter;
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('timeFilter')
                ->label('Periode')
                ->options([
                    'weekly' => 'Mingguan',
                    'monthly' => 'Bulanan',
                ])
                ->default('weekly')
                ->live(),
        ];
    }

    public ?string $filter = 'revenue'; // Defaul FIlter
    protected function getFilters(): ?array
    {
        return [
            'revenue' => 'Pendapatan',
            'purchase' => 'Pembelian',
        ];
    }


    protected function getOptions(): array
    {
        // 1. Ambil nilai dari kedua filter
        $dataType = $this->filter;
        $timeFilter = $this->filterFormData['timeFilter'];

        // 2. Tentukan variabel dinamis
        $transactionType = $dataType === 'revenue' ? TransactionType::SELL->value : TransactionType::PURCHASE->value;
        $isWeekly = $timeFilter === 'weekly';
        $startDate = $isWeekly ? Carbon::now()->startOfMonth() : Carbon::now()->startOfYear();
        $endDate = $isWeekly ? Carbon::now()->endOfMonth() : Carbon::now()->endOfYear();

        // 3. Jalankan kueri yang dioptimalkan
        $transactions = Transaction::query()
            ->where('type', $transactionType)
            ->where('status', TransactionStatus::COMPLETE->value)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select('total_price', 'updated_at')
            ->get();

        // 4. Proses data
        $results = $transactions
            ->groupBy(function ($transaction) use ($isWeekly) {
                $date = Carbon::parse($transaction->updated_at);
                return $isWeekly
                    ? $date->startOfWeek()->translatedFormat('D, d M')
                    : $date->translatedFormat('M');
            })
            ->map(fn($group) => $group->sum('total_price'));

        // 5. Siapkan "cetakan" data
        $periodData = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->isFuture()) break;
            if ($isWeekly) {
                $periodData[$currentDate->startOfWeek()->translatedFormat('D, d M')] = 0;
                $currentDate->addWeek();
            } else {
                $periodData[$currentDate->translatedFormat('M')] = 0;
                $currentDate->addMonth();
            }
        }

        $chartData = array_merge($periodData, $results->toArray());
        $data = array_values($chartData);
        $labels = array_keys($chartData);

        // 6. Atur tampilan dinamis
        $chartLabel = $dataType === 'revenue' ? 'Pendapatan' : 'Pembelian';
        $chartColor = $dataType === 'revenue' ? '#3b82f6' : '#f97316';

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false]
            ],
            'series' => [['name' => $chartLabel, 'data' => $data]],
            'xaxis' => ['categories' => $labels],
            'yaxis' => [
                'title' => [
                    'text' => 'Rupiah (Rp)'
                ]
            ],
            'colors' => [$chartColor],
            'stroke' => ['curve' => 'smooth'],
            'dataLabels' => ['enabled' => false],
        ];
    }

    // Metode terpisah untuk opsi JS agar kode lebih rapi
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
