<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GrossProfitChart extends ApexChartWidget
{

    protected static ?string $chartId = 'grossProfitChart';
    

    public function getHeading(): string
    {
        $chartLabel = $this->filterFormData['timeFilter'] === 'weekly' ? 'Mingguan' : 'Bulanan';
        return 'Grafik Laba Kotor ' . $chartLabel;
    }

    protected static ?string $footer = 'Menampilkan estimasi laba kotor dari transaksi yang telah selesai baik lunas maupun belum lunas.';

    // Filter Form untuk memilih periode
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

    /**
     * Mendefinisikan opsi dan data untuk Apex Chart.
     */
    protected function getOptions(): array
    {
        // 1. Ambil nilai filter dari form
        $timeFilter = $this->filterFormData['timeFilter'];

        // 2. Tentukan variabel dinamis
        $isWeekly = $timeFilter === 'weekly';
        $startDate = $isWeekly ? Carbon::now()->startOfMonth() : Carbon::now()->startOfYear();
        $endDate = $isWeekly ? Carbon::now()->endOfMonth() : Carbon::now()->endOfYear();

        // 3. Jalankan SATU KALI kueri yang dioptimalkan
        $transactions = Transaction::query()
            ->where('status', TransactionStatus::COMPLETE->value)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select('type', 'total_price', 'updated_at')
            ->get();

        // 4. Proses data menggunakan Laravel Collection
        $results = $transactions
            ->groupBy(function ($transaction) use ($isWeekly) {
                $date = Carbon::parse($transaction->updated_at);
                return $isWeekly
                    ? $date->startOfWeek()->translatedFormat('D, d M')
                    : $date->translatedFormat('M');
            })
            ->map(function ($group) {
                $revenue = $group->where('type', TransactionType::SELL)->sum('total_price');
                $purchase = $group->where('type', TransactionType::PURCHASE)->sum('total_price');
                return $revenue - $purchase;
            });

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

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false]
            ],
            'series' => [['name' => 'Laba Kotor', 'data' => $data]],
            'xaxis' => ['categories' => $labels],
            'yaxis' => [
                'title' => [
                    'text' => 'Dalam Rupiah (Rp)'
                ]
            ],
            'colors' => ['#22c55e'], // Hijau
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
