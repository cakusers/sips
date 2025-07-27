<?php

namespace App\Filament\Widgets\Decarbonization;

use App\Enums\MovementType;
use App\Models\StockMovement;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use NumberFormatter;

class CarbonFootPrintOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Waktu
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;

        // Jejak karbon masuk
        $currentMonthCarbonIn = $this->getCarbonFootprintIn($currentMonth);
        $lastMonthCarbonIn = $this->getCarbonFootprintIn($lastMonth);

        // Jejak karbon keluar
        $currentMonthCarbonOut = $this->getCarbonFootprintOut($currentMonth);
        $lastMonthCarbonOut = $this->getCarbonFootprintOut($lastMonth);

        $currentMonthAvgCarbonIn = $this->getAvgCarbonFootprintIn($currentMonth);
        $lastMonthAvgCarbonIn = $this->getAvgCarbonFootprintIn($lastMonth);

        return [
            $this->createStatCard('Jejak Karbon Masuk Bulan Ini', $currentMonthCarbonIn, $lastMonthCarbonIn),
            $this->createStatCard('Jejak Karbon Keluar Bulan Ini', $currentMonthCarbonOut, $lastMonthCarbonOut),
            $this->createStatCard('Rata-rata Bulan Ini', $currentMonthAvgCarbonIn, $lastMonthAvgCarbonIn),
        ];
    }

    protected function createStatCard(string $label, float $currentValue, float $previousValue): Stat
    {
        $percentageChange = $this->calculatePercentageChange($currentValue, $previousValue);
        $formattedValue = $this->getFormatValue($currentValue);

        $stat = Stat::make($label, $formattedValue);

        // Jika perubahan adalah 0, tampilkan status netral
        if (bccomp((string)$percentageChange, '0', 2) === 0) {
            return $stat
                ->description('Tidak ada perubahan dari bulan lalu')
                ->descriptionIcon('heroicon-m-minus')
                ->color('gray');
        }

        $isIncrease = $percentageChange > 0;
        $descriptionText = sprintf(
            '%s%% %s dari bulan lalu',
            number_format(abs($percentageChange), 2, ',', '.'),
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

    protected function getCarbonFootprintIn(int $month): float
    {
        return StockMovement::query()
            ->whereMonth('created_at', $month)
            ->where('quantity_change_kg', '>', '0')
            ->sum('carbon_footprint_change_kg_co2e');
    }

    protected function getCarbonFootprintOut(int $month): float
    {
        $totalOut = StockMovement::query()
            ->whereMonth('created_at', $month)
            ->whereIn('type', [MovementType::SELLOUT, MovementType::MANUALOUT])
            ->sum('carbon_footprint_change_kg_co2e');

        return abs($totalOut);
    }

    protected function getAvgCarbonFootprintIn(int $month): float
    {
        return StockMovement::query()
            ->whereMonth('created_at', $month)
            ->where('quantity_change_kg', '>', '0')
            ->avg('carbon_footprint_change_kg_co2e');
    }

    protected function getFormatValue(float $value): HtmlString
    {
        $decimal_formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::DECIMAL);
        $formatedValue = $decimal_formatter->format($value);
        return new HtmlString($formatedValue . ' Kg CO<sub>2</sub>e/Kg');
    }

    // protected function dynamicDecimalFormat(float $number): string
    // {
    //     if (!is_numeric($number)) {
    //         return '0';
    //     }

    //     $decimalPlaces  = 0;
    //     $numberStr = (string)$number;
    //     // Cek apakah ada desimal dengan mencari posisi karakter '.'
    //     if (strpos($numberStr, '.') !== false) {
    //         // Ambil bagian string setelah '.' dan hitung panjangnya
    //         $decimalPart = substr($numberStr, strpos($numberStr, '.') + 1);
    //         $decimalPlaces = strlen($decimalPart);
    //     }

    //     return number_format($number, $decimalPlaces, ',', '.');
    // }
}
