<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendapatan';
    public ?string $filter = 'weekly';
    protected static ?string $maxHeight = '300px';

    public function getDescription(): ?string
    {
        return 'Menampilkan total pendapatan (penjualan) yang telah selesai.';
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

                // Ambil semua data pendapatan untuk bulan ini
                $dailyRevenues = Transaction::query()
                    ->where('type', TransactionType::SELL->value)
                    ->where('status', TransactionStatus::COMPLETE->value)
                    ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->select(DB::raw('DATE(updated_at) as date'), DB::raw('SUM(total_price) as total'))
                    ->groupBy('date')
                    ->pluck('total', 'date');

                // Array untuk setiap minggu di bulan ini
                $weeklyData = [];
                $currentDate = $startOfMonth->copy();
                while ($currentDate->lte($endOfMonth)) {
                    $startOfWeek = $currentDate->copy()->startOfWeek();
                    if ($startOfWeek->isFuture()) {
                        break;
                    }
                    $weekLabel = $startOfWeek->translatedFormat('D, d M');
                    $weeklyData[$weekLabel] = 0;
                    $currentDate->addWeek();
                }

                // Proses data harian dan masukkan ke minggu yang benar.
                foreach ($dailyRevenues as $date => $total) {
                    $dateCarbon = Carbon::parse($date);
                    $weekLabel = $dateCarbon->startOfWeek()->translatedFormat('D, d M');
                    if (isset($weeklyData[$weekLabel])) {
                        $weeklyData[$weekLabel] += $total;
                    }
                }

                $labels = array_keys($weeklyData);
                $data = array_values($weeklyData);
                break;

            case 'monthly':
                $startOfYear = Carbon::now()->startOfYear();
                $endOfYear = Carbon::now()->endOfYear();

                //Ambil semua data pendapatan untuk tahun ini
                $revenuesByMonth = Transaction::query()
                    ->where('type', TransactionType::SELL->value)
                    ->where('status', TransactionStatus::COMPLETE->value)
                    ->whereBetween('updated_at', [$startOfYear, $endOfYear])
                    ->select(DB::raw('MONTH(updated_at) as month'), DB::raw('SUM(total_price) as total'))
                    ->groupBy('month')
                    ->pluck('total', 'month');

                // Array untuk 12 bulan dengan nilai awal 0.
                $monthlyData = [];
                $currentDate = $startOfYear->copy();
                while ($currentDate->year == $startOfYear->year) {
                    if ($currentDate->isFuture()) {
                        break;
                    }
                    $monthlyData[$currentDate->translatedFormat('M')] = 0;
                    $currentDate->addMonth();
                }

                // Isi "cetakan" dengan data nyata dari hasil kueri.
                foreach ($revenuesByMonth as $monthNumber => $total) {
                    $monthName = Carbon::create()->month($monthNumber)->translatedFormat('M');
                    if (isset($monthlyData[$monthName])) {
                        $monthlyData[$monthName] = $total;
                    }
                }

                // 4. Siapkan data akhir untuk grafik
                $labels = array_keys($monthlyData);
                $data = array_values($monthlyData);
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Mendefinisikan filter yang tersedia untuk widget ini.
     *
     * @return array|null
     */
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
