<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\Select;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendapatan';

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
                    $revenue = $this->getRevenueForPeriod($startOfWeek, $endOfWeek);

                    $data[] = $revenue;
                    $labels[] = $startOfWeek->translatedFormat('D, d M');

                    $currentDate->addWeek();
                }
                break;

            case 'monthly':
                $startOfYear = Carbon::now()->startOfYear();
                $endOfYear = Carbon::now()->endOfYear();
                $currentDate = $startOfYear->copy();

                while ($currentDate->lte($endOfYear)) {
                    $startOfMonth = $currentDate->copy()->startOfMonth();

                    if ($startOfMonth->isFuture()) {
                        break;
                    }

                    $endOfMonth = $currentDate->copy()->endOfMonth();
                    $revenue = $this->getRevenueForPeriod($startOfMonth, $endOfMonth);

                    $data[] = $revenue;
                    $labels[] = $startOfMonth->translatedFormat('M');

                    $currentDate->addMonth();
                }
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
                    'tension' => 0.2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Fungsi helper untuk mengambil total pendapatan pada periode tertentu.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function getRevenueForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        return Transaction::query()
            ->where('type', TransactionType::SELL->value)
            ->where('status', TransactionStatus::COMPLETE->value)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('total_price');
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
