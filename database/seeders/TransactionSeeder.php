<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Waste;
use App\Models\Customer;
use App\Models\WastePrice;
use App\Enums\MovementType;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Models\StockMovement;
use App\Enums\TransactionType;
use Illuminate\Database\Seeder;
use App\Enums\TransactionStatus;
use App\Models\TransactionWaste;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mayStart = Carbon::create(2025, 5, 1)->startOfMonth();
        $juneEnd = Carbon::create(2025, 6, 1)->endOfMonth();
        $wastes = Waste::all();
        $customers = Customer::all();
        $user = User::find(1);

        // Loop setiap hari dari awal Mei hingga akhir Juni
        for ($currentDate = $mayStart->copy(); $currentDate->lessThanOrEqualTo($juneEnd); $currentDate->addDay()) {

            // Kita akan membuat transaksi setiap hari Minggu
            if ($currentDate->isSunday()) {
                $month = $currentDate->month;

                // Membuat transaksi PEMBELIAN
                $this->createTransaction(
                    $wastes->random(),
                    $customers->random(),
                    $user,
                    TransactionType::PURCHASE,
                    $currentDate,
                    $month // Kirim bulan untuk penyesuaian harga
                );

                // Membuat transaksi PENJUALAN
                $this->createTransaction(
                    $wastes->random(),
                    $customers->random(),
                    $user,
                    TransactionType::SELL,
                    $currentDate,
                    $month // Kirim bulan untuk penyesuaian harga
                );
            }
        }
    }
    private function createTransaction(Waste $waste, Customer $customer, User $user, TransactionType $type, Carbon $date, int $month)
    {
        DB::transaction(function () use ($waste, $customer, $user, $type, $date, $month) {
            // Ambil harga terbaru yang berlaku
            $priceInfo = WastePrice::where('waste_id', $waste->id)
                ->where('effective_start_date', '<=', $date)
                ->orderBy('effective_start_date', 'desc')
                ->first();

            if (!$priceInfo) {
                // Lewati jika tidak ada data harga
                return;
            }

            // LOGIKA UTAMA: Kuantitas di bulan Mei dibuat lebih besar daripada bulan Juni
            $quantity = ($month == 5)
                ? rand(50, 150)  // Kuantitas besar untuk bulan Mei
                : rand(10, 60);   // Kuantitas lebih kecil untuk bulan Juni

            $unitPrice = ($type === TransactionType::PURCHASE)
                ? $priceInfo->purchase_per_kg
                : $priceInfo->selling_per_kg;

            $totalPrice = $quantity * $unitPrice;

            // 1. Buat record di tabel transactions
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'type' => $type,
                'status' => TransactionStatus::COMPLETE,
                'payment_status' => PaymentStatus::PAID,
                'total_price' => $totalPrice,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 2. Buat record di tabel pivot transaction_waste
            TransactionWaste::create([
                'transaction_id' => $transaction->id,
                'waste_id' => $waste->id,
                'qty_in_kg' => $quantity,
                'unit_price' => $unitPrice,
                'sub_total_price' => $totalPrice,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 3. Update stok di tabel wastes
            if ($type === TransactionType::PURCHASE) {
                $waste->increment('stock_in_kg', $quantity);
                $movementType = MovementType::PURCHASEIN;
            } else {
                // Pastikan stok cukup sebelum mengurangi
                if ($waste->stock_in_kg < $quantity) {
                    // Jika tidak cukup, kita bisa batalkan transaksi atau sesuaikan kuantitas
                    // Untuk seeder ini, kita anggap stok selalu ada.
                }
                $waste->decrement('stock_in_kg', $quantity);
                $movementType = MovementType::SELLOUT;
            }

            // Ambil stok terbaru setelah diubah
            $currentStock = $waste->fresh()->stock_in_kg;

            // 4. Buat catatan pergerakan stok (stock_movements)
            StockMovement::create([
                'waste_id' => $waste->id,
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'type' => $movementType,
                'quantity_change_kg' => $quantity,
                'current_stock_after_movement_kg' => $currentStock,
                'description' => "Transaksi {$type->value} otomatis oleh seeder.",
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        });
    }
}
