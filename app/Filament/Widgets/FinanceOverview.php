<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use Illuminate\Support\Number;
use App\Enums\TransactionStatus;
use App\Traits\WidgetFormattingHelper;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FinanceOverview extends BaseWidget
{
    use WidgetFormattingHelper;

    /**
     * Metode utama untuk menghasilkan statistik.
     * Mengorkestrasi pengambilan data, perhitungan, dan pembuatan kartu.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        // $fakeNow = Carbon::create(2025, 5, 31);
        // Carbon::setTestNow($fakeNow);

        try {
            // Tentukan periode waktu untuk bulan ini dan bulan lalu
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();
            $previousMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
            $previousMonthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();

            // Ambil data pendapatan (penjualan)
            $currentMonthRevenue = $this->getTransactionTotal($currentMonthStart, $currentMonthEnd, TransactionType::SELL);
            $previousMonthRevenue = $this->getTransactionTotal($previousMonthStart, $previousMonthEnd, TransactionType::SELL);

            // Ambil data pengeluaran (pembelian)
            $currentMonthPurchases = $this->getTransactionTotal($currentMonthStart, $currentMonthEnd, TransactionType::PURCHASE);
            $previousMonthPurchases = $this->getTransactionTotal($previousMonthStart, $previousMonthEnd, TransactionType::PURCHASE);

            // Hitung laba kotor
            $currentMonthGrossProfit = $currentMonthRevenue - $currentMonthPurchases;
            $previousMonthGrossProfit = $previousMonthRevenue - $previousMonthPurchases;

            // dd([$currentMonthGrossProfit, $previousMonthGrossProfit]);

            // Buat dan kembalikan kartu statistik
            return [
                $this->createStatCard('Pendapatan Bulan Ini', $currentMonthRevenue, $previousMonthRevenue),
                $this->createStatCard('Pembelian Bulan Ini', $currentMonthPurchases, $previousMonthPurchases),
                $this->createStatCard('Estimasi Laba Kotor Bulan Ini', $currentMonthGrossProfit, $previousMonthGrossProfit),
            ];
        } finally {
            // --- PENTING: Selalu reset waktu setelah selesai ---
            // Ini akan mengembalikan Carbon::now() ke waktu sebenarnya.
            // Carbon::setTestNow();
        }
    }

    /**
     * Menghitung total transaksi (penjualan atau pembelian) dalam rentang tanggal tertentu.
     *
     * @param Carbon $startDate Tanggal mulai periode.
     * @param Carbon $endDate Tanggal akhir periode.
     * @param TransactionType $type Tipe transaksi (SELL atau PURCHASE).
     * @return float Total nilai transaksi.
     */
    private function getTransactionTotal(Carbon $startDate, Carbon $endDate, TransactionType $type): float
    {
        return Transaction::query()
            ->where('type', $type->value)
            ->where('status', TransactionStatus::COMPLETE->value)
            // Menggunakan updated_at karena status complete diatur saat itu.
            // Jika Anda memiliki 'completed_at', itu lebih baik.
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('total_price');
    }

    /**
     * Membuat satu kartu statistik (Stat object).
     * Fungsi ini mengurus logika untuk menampilkan nilai, deskripsi, ikon, dan warna.
     *
     * @param string $label Judul untuk kartu.
     * @param float $currentValue Nilai untuk periode saat ini.
     * @param float $previousValue Nilai untuk periode sebelumnya (untuk perbandingan).
     * @return Stat Objek kartu statistik yang sudah dikonfigurasi.
     */
    private function createStatCard(string $label, float $currentValue, float $previousValue): Stat
    {
        $percentageChange = $this->calculatePercentageChange($currentValue, $previousValue);

        // Format mata uang ke Rupiah tanpa angka desimal (koma).
        $formattedValue = 'Rp ' . number_format($currentValue, 0, ',', '.');

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
            // Menggunakan format angka Indonesia (koma untuk desimal)
            number_format(abs($percentageChange), 2, ',', '.'),
            $isIncrease ? 'kenaikan' : 'penurunan'
        );

        return $stat
            ->description($descriptionText)
            ->descriptionIcon($isIncrease ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($isIncrease ? 'success' : 'danger');
    }

    /**
     * Menghitung persentase perubahan antara dua nilai.
     * Menangani kasus pembagian dengan nol dan nilai negatif secara aman.
     *
     * @param float $current Nilai saat ini.
     * @param float $previous Nilai sebelumnya.
     * @return float Persentase perubahan.
     */
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

        // return (($current - $previous) / $previous) * 100;
        return $result;
    }
}
