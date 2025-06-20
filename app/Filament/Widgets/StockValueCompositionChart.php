<?php

namespace App\Filament\Widgets;

use App\Models\Waste;
use App\Models\WastePrice;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StockValueCompositionChart extends ChartWidget
{
    protected static ?string $heading = 'Komposisi Nilai Stok Teratas';
    protected static ?string $maxHeight = '250px';
    public function getDescription(): ?string
    {
        return 'Menunjukkan potensi nilai jual dari 10 jenis sampah teratas yang ada di stok saat ini.';
    }

    protected function getData(): array
    {
        // 1. Buat subquery untuk mendapatkan harga jual TERBARU untuk setiap jenis sampah.
        // Ini adalah langkah penting untuk memastikan kita menggunakan harga yang relevan.
        $latestPricesSubQuery = WastePrice::select(
            'waste_id',
            DB::raw('MAX(effective_start_date) as max_date')
        )->groupBy('waste_id');

        // 2. Lakukan satu kueri utama untuk menghitung potensi nilai setiap stok.
        // Ini adalah pendekatan yang dioptimalkan untuk menghindari N+1 problem.
        $wasteValues = Waste::query()
            // Gabungkan tabel waste dengan harga jual terbaru menggunakan subquery di atas.
            ->joinSub($latestPricesSubQuery, 'latest_prices', function ($join) {
                $join->on('wastes.id', '=', 'latest_prices.waste_id');
            })
            ->join('waste_prices', function ($join) {
                $join->on('latest_prices.waste_id', '=', 'waste_prices.waste_id');
                $join->on('latest_prices.max_date', '=', 'waste_prices.effective_start_date');
            })
            // Hanya ambil sampah yang memiliki stok.
            ->where('wastes.stock_in_kg', '>', 0)
            // Hitung potensi nilai langsung di database dan urutkan.
            ->selectRaw('wastes.name, (wastes.stock_in_kg * waste_prices.selling_per_kg) as potential_value')
            ->orderByDesc('potential_value')
            ->get();

        if ($wasteValues->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Nilai Stok',
                        'data' => [1], // Beri nilai dummy agar grafik ter-render
                    ],
                ],
                'labels' => ['Tidak ada data stok untuk ditampilkan'],
            ];
        }

        // 3. Proses data untuk ditampilkan di grafik.
        $labels = [];
        $data = [];

        // Ambil 10 data teratas.
        $topTenWastes = $wasteValues->take(10);
        foreach ($topTenWastes as $waste) {
            $labels[] = $waste->name;
            $data[] = $waste->potential_value;
        }

        // Jika ada lebih dari 10, gabungkan sisanya menjadi "Lainnya".
        $otherWastesValue = $wasteValues->skip(10)->sum('potential_value');
        if ($otherWastesValue > 0) {
            $labels[] = 'Lainnya';
            $data[] = $otherWastesValue;
        }

        $colors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#8D6E63',
            '#EC407A',
            '#7E57C2',
            '#66BB6A',
            '#FFA726'
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Nilai Stok',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += 'Rp' + context.parsed.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            }
        }
        JS);
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
