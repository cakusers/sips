<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Widgets\ChartWidget;

class PurchaseChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pembelian';

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
                    $purchase = $this->getPurchaseTotalForPeriod($startOfWeek, $endOfWeek);
                    $data[] = $purchase;
                    $labels[] = $startOfWeek->translatedFormat('D, d');
                    $currentDate->addWeek();
                }
                break;

            case 'monthly':
                for ($i = 12; $i >= 0; $i--) {
                    $startOfMonth = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
                    $endOfMonth = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();
                    $purchase = $this->getPurchaseTotalForPeriod($startOfMonth, $endOfMonth);
                    $data[] = $purchase;
                    $labels[] = $startOfMonth->translatedFormat('M');
                }
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pembelian',
                    'data' => $data,
                    'borderColor' => '#f97316', // Oranye
                    'backgroundColor' => 'rgba(249, 115, 22, 0.2)',
                    'tension' => 0.2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getPurchaseTotalForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        return Transaction::query()
            // **PERBEDAAN UTAMA ADA DI SINI**
            ->where('type', TransactionType::PURCHASE->value)
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
