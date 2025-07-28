<?php

namespace App\Services;

use NumberFormatter;

class NumberService
{
    private string $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function currency(float|int $number, string $currencyCode = 'IDR'): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($number, $currencyCode);
    }

    public function percent(float|int $number, bool $limitFraction = false, int $fractionDigits = 2): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::PERCENT);
        if ($limitFraction) {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
        }
        return $formatter->format($number);
    }

    public function decimal(float|int $number, bool $limitFraction = false, int $fractionDigits = 2): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
        if ($limitFraction) {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
        }
        return $formatter->format($number);
    }
}
