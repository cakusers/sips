<?php

namespace App\Filament\Widgets;

use App\Models\Waste;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class WasteMarginChart extends ApexChartWidget
{

    protected static ?string $chartId = 'wasteMarginChart';

    protected static ?string $heading = 'Margin Keuntungan Tertinggi per Kg';
    protected static ?string $footer = 'Menampilkan 7 sampah dengan nilai keuntungan tertinggi saat ini';

    protected int | string | array $columnSpan = 'full';


    protected function getOptions(): array
    {
        // 1. Ambil semua data sampah beserta relasi harganya (dioptimalkan dengan eager loading)
        $wastesWithPrices = Waste::with('wastePrices')->get();

        // 2. Hitung margin untuk setiap sampah menggunakan Laravel Collection
        $margins = $wastesWithPrices->map(function ($waste) {
            // Ambil harga terbaru dari relasi yang sudah di-load
            $latestPrice = $waste->wastePrices->sortByDesc('effective_start_date')->first();

            // Lanjutkan hanya jika ada harga
            if ($latestPrice) {
                return [
                    'name' => $waste->name,
                    'margin' => $latestPrice->selling_per_kg - $latestPrice->purchase_per_kg,
                ];
            }
            return null;
        })
            // Hilangkan sampah yang tidak punya harga
            ->filter()
            // Urutkan berdasarkan margin tertinggi
            ->sortByDesc('margin')
            // Ambil 7 teratas
            ->take(7);

        // Jika tidak ada data, tampilkan pesan
        if ($margins->isEmpty()) {
            return [
                'chart' => [
                    'type' => 'bar',
                    'height' => 300
                ],
                'series' => [],
                'labels' => ['Tidak ada data margin untuk ditampilkan'],
            ];
        }

        // 3. Siapkan data untuk grafik
        $data = $margins->pluck('margin')->all();
        $labels = $margins->pluck('name')->all();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Margin per Kg',
                    'data' => $data,
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 4,
                ]
            ],
            'xaxis' => [
                'categories' => $labels,
                'title' => [
                    'text' => 'Rupiah (Rp)'

                ]
            ],
            'yaxis' => [
                'labels' => [
                    'style' => ['colors' => '#9ca3af', 'fontWeight' => 600],
                ],
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            dataLabels: {
                formatter: function (val) {
                        return val.toLocaleString('id-ID');
                    }
            },
            xaxis: {
                labels: {
                    formatter: function (val) {
                        return val.toLocaleString('id-ID');
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString('id-ID');
                    }
                }
            }
        }
        JS);
    }
}
