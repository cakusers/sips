<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Illuminate\Support\HtmlString;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\TransactionResource;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    // public function getTitle(): string | Htmlable
    // {
    //     $svg = svg('heroicon-o-star')->toHtml();
    //     $label = $this;
    //     dd($label);

    //     return 'tes';
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
