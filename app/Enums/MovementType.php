<?php

namespace App\Enums;

enum MovementType: string
{
    case PURCHASEIN     = 'purchase_in';            // Masuk dari transaksi pembelian
    case SELLOUT        = 'sell_out';               // Keluar dari transaksi penjualan
    case RETURNEDIN     = 'returned_in';            // Masuk dari transaksi penjualan yang dikembalikan
    case RETURNEDOUT    = 'returned_out';           // Keluar dari transaksi pembelian yang dikembalikan
    case MANUALIN       = 'manual_adjustment_in';   // Masuk dari penyesuaian manual (misalnya: koreksi, ditemukan kembali)
    case MANUALOUT      = 'manual_adjustment_out';  // Keluar dari penyesuaian manual (misalnya: rusak, hilang, koreksi)
    case SORTINGIN      = 'sorting_in';             // Masuk dari pemilahan sampah campuran (Hasil Pemilahan)
}
