<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class GrossProfitChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Laba Kotor';

    public ?string $filter = 'weekly';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $data = [];
        $labels = [];

        switch ($activeFilter) {
            case 'weekly':
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $currentDate = $startOfMonth->copy();

                while ($currentDate->lte($endOfMonth)) {
                    $startOfWeek = $currentDate->copy()->startOfWeek();

                    if ($startOfWeek->isFuture()) {
                        break;
                    }
                    $endOfWeek = $currentDate->copy()->endOfWeek();

                    // Hitung pendapatan dan pembelian untuk periode ini
                    $revenue = $this->getTransactionTotalForPeriod($startOfWeek, $endOfWeek, TransactionType::SELL);
                    $purchase = $this->getTransactionTotalForPeriod($startOfWeek, $endOfWeek, TransactionType::PURCHASE);

                    // Laba kotor adalah selisihnya
                    $grossProfit = $revenue - $purchase;

                    $data[] = $grossProfit;
                    $labels[] = "Minggu " . $startOfWeek->format('d-m-Y');
                    $currentDate->addWeek();
                }
                break;

            case 'monthly':
                for ($i = 5; $i >= 0; $i--) {
                    $startOfMonth = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
                    $endOfMonth = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();

                    // Hitung pendapatan dan pembelian untuk periode ini
                    $revenue = $this->getTransactionTotalForPeriod($startOfMonth, $endOfMonth, TransactionType::SELL);
                    $purchase = $this->getTransactionTotalForPeriod($startOfMonth, $endOfMonth, TransactionType::PURCHASE);

                    // Laba kotor adalah selisihnya
                    $grossProfit = $revenue - $purchase;

                    $data[] = $grossProfit;
                    $labels[] = $startOfMonth->translatedFormat('M Y');
                }
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Laba Kotor',
                    'data' => $data,
                    'borderColor' => '#22c55e', // Hijau
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getTransactionTotalForPeriod(Carbon $startDate, Carbon $endDate, TransactionType $type): float
    {
        return Transaction::query()
            ->where('type', $type->value)
            ->where('status', TransactionStatus::COMPLETE->value)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('total_price');
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
