<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use App\Traits\WidgetFormattingHelper;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FinanceOverview extends BaseWidget
{
    use WidgetFormattingHelper;

    private function getRevenuePeriode(Carbon $startDate, Carbon $endDate): float
    {
        $revenue = Transaction::where('type', TransactionType::SELL->value)
            ->where('status', TransactionStatus::COMPLETE->value)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');
        return (float) ($revenue ?? 0.0);
    }

    private function getPurchaseValuePeriode(Carbon $startDate, Carbon $endDate): float
    {
        $purchaseValue = Transaction::where('type', TransactionType::PURCHASE->value) // Filter tipe PURCHASE
            ->where('status', TransactionStatus::COMPLETE->value) // Hanya pembelian yang selesai
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');
        return (float) ($purchaseValue ?? 0.0);
    }

    private function getProfitPeriode(Carbon $startDate, Carbon $endDate): float
    {
        $profit = Transaction::query()
            ->join('transaction_waste', 'transactions.id', '=', 'transaction_waste.transaction_id')
            ->join('waste_prices', 'transaction_waste.waste_id', '=', 'waste_prices.waste_id')
            ->where('transactions.type', TransactionType::SELL->value)
            ->where('transactions.status', TransactionStatus::COMPLETE->value)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->selectRaw('SUM(transaction_waste.sub_total_price - (transaction_waste.qty_in_kg * waste_prices.purchase_per_kg)) as total_profit')
            ->value('total_profit');
        return (float) ($profit ?? 0.0);
    }

    protected function getStats(): array
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 16, 10, 0, 0));

        try {
            // --- Tanggal ---
            $currentMonthStartDate = Carbon::now()->startOfMonth();
            $currentMonthEndDate   = Carbon::now()->endOfMonth();
            $lastMonthStartDate = Carbon::now()->subMonthNoOverflow()->startOfMonth();
            $lastMonthEndDate   = Carbon::now()->subMonthNoOverflow()->endOfMonth();

            // --- Kalkulasi Revenue / Pendapatan ---
            $revenueThisMonth = $this->getRevenuePeriode($currentMonthStartDate, $currentMonthEndDate);
            $revenueLastMonth = $this->getRevenuePeriode($lastMonthStartDate, $lastMonthEndDate);
            $revenuePercentage = $this->calculatePercentage($revenueThisMonth, $revenueLastMonth);
            $revenueResult = $this->getResult($revenuePercentage, $revenueThisMonth, $revenueLastMonth);

            // --- Kalkulasi Pengeluaran ---
            $purchaseValueThisMonth = $this->getPurchaseValuePeriode($currentMonthStartDate, $currentMonthEndDate);
            $purchaseValueLastMonth = $this->getPurchaseValuePeriode($lastMonthStartDate, $lastMonthEndDate);
            $purchasePercentage = $this->calculatePercentage($purchaseValueThisMonth, $purchaseValueLastMonth);
            $purchaseResult = $this->getResult($purchasePercentage, $purchaseValueThisMonth, $purchaseValueLastMonth);

            // --- Kalkulasi Laba Kotor ---
            $profitThisMonth = $this->getProfitPeriode($currentMonthStartDate, $currentMonthEndDate);
            $profitLastMonth = $this->getProfitPeriode($lastMonthStartDate, $lastMonthEndDate);
            $profitPercentage = $this->calculatePercentage($profitThisMonth, $profitLastMonth);
            // dd([$profitThisMonth, $profitLastMonth, $profitPercentage]);
            $profitResult = $this->getResult($profitPercentage, $profitThisMonth, $profitLastMonth);

            return [
                // Stat untuk Pendapatan
                Stat::make('Pendapatan Bulan Ini', $this->rupiahFormat($revenueThisMonth))
                    ->description($revenueResult['descriptionText'])
                    ->descriptionIcon($revenueResult['descriptionIcon'], IconPosition::Before)
                    ->color($revenueResult['color'])
                    ->chart([2, 2, 2, 2]),

                // Stat untuk Pengeluaran
                Stat::make('Pembelian Bulan Ini', $this->rupiahFormat($purchaseValueThisMonth))
                    ->description($purchaseResult['descriptionText'] . ' dari bulan lalu')
                    ->descriptionIcon($purchaseResult['descriptionIcon'], IconPosition::Before)
                    ->color($purchaseResult['color'])
                    ->chart([2, 2, 2, 2]),

                // Stat untuk Laba Kotor
                Stat::make('Laba Kotor Bulan Ini', $this->rupiahFormat($profitThisMonth))
                    ->description($profitResult['descriptionText'])
                    ->descriptionIcon($profitResult['descriptionIcon'], IconPosition::Before)
                    ->color($profitResult['color'])
                    ->chart([2, 2, 2, 2]),
            ];
        } finally {
            Carbon::setTestNow();
        }
    }
}
