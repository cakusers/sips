<?php

namespace App\Traits;

trait WidgetFormattingHelper
{
    private function rupiahFormat(float $value): string
    {
        return 'Rp' . number_format($value, 0, ',', '.');
    }

    private function percentageFormat(float $value): string
    {
        return number_format(abs($value), 2) . '%';
    }

    private function calculatePercentage(float $currentValue, float $previousValue): float
    {
        $percentageChange = 0.0;

        if ($previousValue > 0) {
            $percentageChange = (($currentValue - $previousValue) / $previousValue) * 100;
        } elseif ($currentValue > 0 && $previousValue == 0) {
            // Kenaikan dari 0 ke nilai positif
            $percentageChange = 100.0;
        } elseif ($currentValue < 0 && $previousValue == 0) {
            // Penurunan dari 0 ke nilai negatif (misal laba jadi rugi)
            $percentageChange = -100.0;
        }
        // Jika $currentValue == 0 dan $previousValue == 0, $percentageChange tetap 0.0
        // Jika $currentValue == 0 dan $previousValue > 0, $percentageChange akan jadi -100.0

        return $percentageChange;
    }


    private function getResult(float $percentage, float $currentValue = 0, float $previousValue = 0): array
    {
        $descriptionText = '';
        $descriptionIcon = '';
        $color = 'gray';

        if ($currentValue == 0 && $previousValue == 0 && $percentage == 0) {
            $descriptionText = 'Tidak ada data';
            $descriptionIcon = 'heroicon-m-no-symbol';
        } elseif ($percentage > 0) {
            $descriptionText = 'Naik ' . $this->percentageFormat($percentage);
            $descriptionIcon = 'heroicon-m-arrow-trending-up';
            $color = 'success';
        } elseif ($percentage < 0) {
            $descriptionText = 'Turun ' . $this->percentageFormat($percentage);
            $descriptionIcon = 'heroicon-m-arrow-trending-down';
            $color = 'danger';
        } else { // percentage == 0 (dan bukan kasus keduanya 0)
            $descriptionText = 'Stabil ' . $this->percentageFormat($percentage);
            $descriptionIcon = 'heroicon-m-minus';
        }

        return ['descriptionText' => $descriptionText, 'descriptionIcon' => $descriptionIcon, 'color' => $color];
    }
}
