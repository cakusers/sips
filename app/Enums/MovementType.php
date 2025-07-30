<?php

namespace App\Enums;

enum MovementType: string
{
    case PURCHASEIN     = 'purchase_in';            // Masuk dari transaksi pembelian (Bertambah)
    case SELLOUT        = 'sell_out';               // Keluar dari transaksi penjualan (Berkurang)
    case RETURNEDIN     = 'returned_in';            // Masuk dari transaksi penjualan yang dikembalikan (Bertambah)
    case RETURNEDOUT    = 'returned_out';           // Keluar dari transaksi pembelian yang dikembalikan (Berkurang)
    case MANUALIN       = 'manual_adjustment_in';   // Masuk dari penyesuaian manual (misalnya: koreksi, ditemukan kembali) (Bertambah)
    case MANUALOUT      = 'manual_adjustment_out';  // Keluar dari penyesuaian manual (misalnya: rusak, hilang, koreksi) (Berkurang)
    case SORTINGIN      = 'sorting_in';             // Masuk dari pemilahan sampah campuran (Hasil Pemilahan) (Bertambah)
    case SORTINGOUT     = 'sorting_out';            // Keluar dari pemilahan sampah campuran (Sampah Campuran Awal) (Berkurang)
    case SORTINGADJUST  = 'sorting_adjust';            // Keluar dari pemilahan sampah campuran (Sampah Campuran Awal) (Bertambah \ Berkurang)
}
