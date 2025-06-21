<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Widgets\ChartWidget;

class PurchaseChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pembelian';
    public ?string $filter = 'weekly';
    protected static ?string $maxHeight = '300px';

    public function getDescription(): ?string
    {
        return 'Menampilkan total pembelian sampah yang telah selesai.';
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

                // Ambil data pembelian bulan ini
                $transactions = Transaction::query()
                    ->where('type', TransactionType::PURCHASE->value)
                    ->where('status', TransactionStatus::COMPLETE->value)
                    ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->select('total_price', 'updated_at')
                    ->get();

                // Menjumlahkan menggunakan Eloquent
                $purchaseByWeek = $transactions
                    ->groupBy(function ($transaction) {
                        return Carbon::parse($transaction->updated_at)->startOfWeek()->translatedFormat('D, d M');
                    })
                    ->map(fn($group) => $group->sum('total_price'));

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
                $chartData = array_merge($weeklyData, $purchaseByWeek->toArray());

                $labels = array_keys($chartData);
                $data = array_values($chartData);
                break;

            case 'monthly':
                $startOfYear = Carbon::now()->startOfYear();
                $endOfYear = Carbon::now()->endOfYear();

                // Ambil data pembelian tahun ini
                $transactions = Transaction::query()
                    ->where('type', TransactionType::PURCHASE->value)
                    ->where('status', TransactionStatus::COMPLETE->value)
                    ->whereBetween('updated_at', [$startOfYear, $endOfYear])
                    ->select('total_price', 'updated_at')
                    ->get();

                // Menjumlahkan menggunakan Eloquent
                $purchaseByMonth = $transactions
                    ->groupBy(fn($transaction) => Carbon::parse($transaction->updated_at)->translatedFormat('M'))
                    ->map(fn($group) => $group->sum('total_price'));

                // Siapkan array per bulan
                $monthlyData = [];
                $currentDate = $startOfYear->copy();
                while ($currentDate->lte($endOfYear)) {
                    if ($currentDate->isFuture()) break;
                    $monthlyData[$currentDate->translatedFormat('M')] = 0;
                    $currentDate->addMonth();
                }

                // Penggabungan dengan array
                $chartData = array_merge($monthlyData, $purchaseByMonth->toArray());

                $labels = array_keys($chartData);
                $data = array_values($chartData);
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pembelian',
                    'data' => $data,
                    'borderColor' => '#f97316', // Oranye
                    'fill' => true,
                    'backgroundColor' => 'rgba(249, 115, 22, 0.2)',
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
