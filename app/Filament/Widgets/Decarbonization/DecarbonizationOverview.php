<?php

namespace App\Filament\Widgets\Decarbonization;

use Carbon\Carbon;
use App\Services\NumberService;
use App\Enums\MovementType;
use App\Models\StockMovement;
use Illuminate\Support\HtmlString;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DecarbonizationOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $fakeNow = Carbon::create(2025, 7, 30);
        Carbon::setTestNow($fakeNow);
        try {
            // Waktu
            $currentMonth = Carbon::now()->month;
            $lastMonth = Carbon::now()->subMonthNoOverflow()->month;

            // Jejak karbon masuk
            $currentMonthCarbonIn = $this->getDecarbonizationIn($currentMonth);
            $lastMonthCarbonIn = $this->getDecarbonizationIn($lastMonth);

            // Jejak karbon keluar
            $currentMonthCarbonOut = $this->getDecarbonizationOut($currentMonth);
            $lastMonthCarbonOut = $this->getDecarbonizationOut($lastMonth);

            $currentMonthAvgCarbonOut = $this->getAvgCarbonFootprintOut($currentMonth);
            $lastMonthAvgCarbonOut = $this->getAvgCarbonFootprintOut($lastMonth);

            return [
                $this->createStatCard('Dekarbonisasi Masuk Bulan Ini', $currentMonthCarbonIn, $lastMonthCarbonIn, app(NumberService::class)),
                $this->createStatCard('Dekarbonisasi Keluar Bulan Ini', $currentMonthCarbonOut, $lastMonthCarbonOut, app(NumberService::class)),
                $this->createStatCard('Rata-Rata Dekarbonisasi Keluar Bulan Ini', $currentMonthAvgCarbonOut, $lastMonthAvgCarbonOut, app(NumberService::class)),
            ];
        } finally {
            Carbon::setTestNow();
        }
    }

    protected function createStatCard(string $label, float $currentValue, float $previousValue, NumberService $numberService): Stat
    {
        $percentageChange = $this->calculatePercentageChange($currentValue, $previousValue);
        $formattedValue = $this->getFormatValue($currentValue, $numberService);

        $stat = Stat::make($label, $formattedValue);

        // Jika perubahan adalah 0, tampilkan status netral
        if (bccomp((string)$percentageChange, '0', 2) === 0) {
            return $stat
                ->description('Tidak ada perubahan dari bulan lalu')
                ->descriptionIcon('heroicon-m-minus')
                ->color('gray');
        }

        $isIncrease = $percentageChange > 0;
        $percentageFormatted = $numberService->decimal(abs($percentageChange), true);

        $descriptionText = sprintf(
            '%s%% %s dari bulan lalu',
            $percentageFormatted,
            $isIncrease ? 'kenaikan' : 'penurunan'
        );

        return $stat
            ->description($descriptionText)
            ->descriptionIcon($isIncrease ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($isIncrease ? 'success' : 'danger');
    }

    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            if ($current > 0) {
                // Dari 0 ke positif adalah kenaikan 100%
                return 100.0;
            }
            if ($current < 0) {
                // Dari 0 ke negatif adalah penurunan 100%
                return -100.0;
            }
            // Dari 0 ke 0 tidak ada perubahan
            return 0.0;
        }

        $result = ($current - $previous) / abs($previous) * 100;
        return $result;
    }

    protected function getDecarbonizationIn(int $month): float
    {

        return StockMovement::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('quantity_change_kg', '>', '0')
            ->sum('carbon_footprint_change_kg_co2e');
    }

    protected function getDecarbonizationOut(int $month): float
    {
        $totalOut = StockMovement::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereIn('type', [MovementType::SELLOUT, MovementType::MANUALOUT])
            ->sum('carbon_footprint_change_kg_co2e');

        return abs($totalOut);
    }

    protected function getAvgCarbonFootprintOut(int $month): float
    {
        $avgCarbonFootprintOut =  StockMovement::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('quantity_change_kg', '<', '0')
            ->avg('carbon_footprint_change_kg_co2e');

        return is_null($avgCarbonFootprintOut) ? 0 : abs($avgCarbonFootprintOut);
    }

    protected function getFormatValue(float $value, NumberService $numberService): HtmlString
    {
        $formattedValue = $numberService->decimal($value);
        return new HtmlString($formattedValue . ' Kg CO<sub>2</sub>e');
    }
}
