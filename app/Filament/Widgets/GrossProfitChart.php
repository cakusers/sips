<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Widgets\ChartWidget;

class GrossProfitChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Laba Kotor';
    public ?string $filter = 'weekly';
    protected static ?string $maxHeight = '300px';

    public function getDescription(): ?string
    {
        return 'Menampilkan laba kotor (Pendapatan - Pembelian) dari transaksi yang telah selesai.';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return 'Rp ' + (value / 1000000000).toFixed(1).replace(/\.0$/, '').replace('.', ',') + ' Miliar';
                            }
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1).replace(/\.0$/, '').replace('.', ',') + ' Juta';
                            }
                            if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(1).replace(/\.0$/, '').replace('.', ',') + ' Ribu';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            },
        }
        JS);
    }


    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $data = [];
        $labels = [];

        switch ($activeFilter) {
            case 'weekly':
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();

                // Ambil semua transaksi (jual & beli) untuk bulan ini.
                $transactions = Transaction::query()
                    ->where('status', TransactionStatus::COMPLETE->value)
                    ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->select('type', 'total_price', 'updated_at')
                    ->get();

                // Kelompokkan berdasarkan minggu, lalu hitung laba kotor.
                $profitByWeek = $transactions
                    ->groupBy(function ($transaction) {
                        return Carbon::parse($transaction->updated_at)->startOfWeek()->translatedFormat('D, d M');
                    })
                    ->map(function ($group) {
                        // Untuk setiap minggu, hitung total penjualan dan pembelian, lalu cari selisihnya.
                        $revenue = $group->where('type', TransactionType::SELL)->sum('total_price');
                        $purchase = $group->where('type', TransactionType::PURCHASE)->sum('total_price');
                        return $revenue - $purchase;
                    });

                // Siapkan array per minggu
                $weeklyData = [];
                $currentDate = $startOfMonth->copy();
                while ($currentDate->lte($endOfMonth)) {
                    $startOfWeek = $currentDate->copy()->startOfWeek();
                    if ($startOfWeek->isFuture()) break;
                    $weeklyData[$startOfWeek->translatedFormat('D, d M')] = 0;
                    $currentDate->addWeek();
                }

                // Penggabungan dengan array
                $chartData = array_merge($weeklyData, $profitByWeek->toArray());

                $labels = array_keys($chartData);
                $data = array_values($chartData);
                break;

            case 'monthly':
                $startOfYear = Carbon::now()->startOfYear();
                $endOfYear = Carbon::now()->endOfYear();

                // 1. Satu kueri cepat: Ambil SEMUA transaksi (jual & beli) untuk tahun ini.
                $transactions = Transaction::query()
                    ->where('status', TransactionStatus::COMPLETE->value)
                    ->whereBetween('updated_at', [$startOfYear, $endOfYear])
                    ->select('type', 'total_price', 'updated_at')
                    ->get();

                // 2. Olah di PHP: Kelompokkan berdasarkan bulan, lalu hitung laba kotor.
                $profitByMonth = $transactions
                    ->groupBy(fn($transaction) => Carbon::parse($transaction->updated_at)->translatedFormat('M'))
                    ->map(function ($group) {
                        $revenue = $group->where('type', TransactionType::SELL)->sum('total_price');
                        $purchase = $group->where('type', TransactionType::PURCHASE)->sum('total_price');
                        return $revenue - $purchase;
                    });

                // 3. Siapkan "cetakan" untuk semua bulan dalam setahun.
                $monthlyData = [];
                $currentDate = $startOfYear->copy();
                while ($currentDate->lte($endOfYear)) {
                    if ($currentDate->isFuture()) break;
                    $monthlyData[$currentDate->translatedFormat('M')] = 0;
                    $currentDate->addMonth();
                }

                // 4. Gabungkan hasil nyata dengan cetakan.
                $chartData = array_merge($monthlyData, $profitByMonth->toArray());

                $labels = array_keys($chartData);
                $data = array_values($chartData);
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Laba Kotor',
                    'data' => $data,
                    'borderColor' => '#22c55e',
                    'fill' => true,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
