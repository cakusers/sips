<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionWIdget extends BaseWidget
{
    protected function getStats(): array
    {
        $unfinishedTransaction = $this->getUnfinishedTransaction();
        $unpaidTransaction = $this->getUnpaidTransaction();

        return [
            Stat::make('Jumlah Transaksi Belum Selesai', $unfinishedTransaction)
                ->description('Jumlah transaksi yang perlu diproses lebih lanjut.')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->url(TransactionResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => [
                            'values' => [
                                TransactionStatus::NEW,
                                TransactionStatus::DELIVERED
                            ],
                        ],
                    ],
                ])),
            Stat::make('Jumlah Transaksi Belum DIbayar', $unpaidTransaction)
                ->description('Jumlah transaksi yang pembayarannya masih tertunda.')
                ->descriptionIcon('heroicon-m-banknotes')
                ->url(TransactionResource::getUrl('index', [
                    'tableFilters' => [
                        'payment_status' => [
                            'value' => PaymentStatus::UNPAID->value,
                        ],
                        'status' => [
                            'values' => [
                                TransactionStatus::NEW,
                                TransactionStatus::DELIVERED,
                                TransactionStatus::COMPLETE,
                                TransactionStatus::RETURNED,
                            ],
                        ]
                    ],
                ])),
        ];
    }

    /**
     * Dapatkan semua transaksi yang belum selesai (New dan Delivered)
     */
    protected function getUnfinishedTransaction(): int
    {
        return Transaction::query()
            ->whereIn('status', [
                TransactionStatus::NEW,
                TransactionStatus::DELIVERED
            ])
            ->count();
    }

    /**
     * Dapatkan semua transaksi yang belum dibayar dan tidak dibatalkan
     */
    protected function getUnpaidTransaction(): int
    {
        return Transaction::query()
            ->where('payment_status', PaymentStatus::UNPAID)
            ->where('status', '!=', TransactionStatus::CANCELED)
            ->count();
    }
}
