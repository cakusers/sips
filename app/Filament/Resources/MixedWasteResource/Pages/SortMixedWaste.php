<?php

namespace App\Filament\Resources\MixedWasteResource\Pages;

use Closure;
use App\Models\Waste;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Enums\MovementType;
use App\Models\StockMovement;
use App\Enums\TransactionStatus;
use App\Models\TransactionWaste;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\MixedWasteResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class SortMixedWaste extends Page implements HasForms
{
    use InteractsWithRecord;
    use InteractsWithForms;

    protected static string $resource = MixedWasteResource::class;
    protected static string $view = 'filament.resources.mixed-waste-resource.pages.sort-mixed-waste';
    // protected static ?string $title = 'Pilah Sampah';
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Cek apakah transaksi dikembalikan atau dibatalkan

        // Jika sudah dipilah tampilkan sampahnya
        $loadData = ['transaction.customer', 'transaction.customerCategory'];
        if ($this->record->is_sorted) {
            array_push($loadData, 'sortedWastes');
        }

        $this->form->fill(
            $this->record->load($loadData)->toArray()
        );
    }

    public function getTitle(): string | Htmlable
    {
        return sprintf('Sortir Sampah %s', $this->record->waste->name);
    }

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('status_display')
                ->label(function () {
                    $status = match ($this->record->transaction->status) {
                        TransactionStatus::CANCELED => 'dibatalkan',
                        TransactionStatus::RETURNED => 'dikembalikan'
                    };

                    return sprintf("Sampah dari transaksi yang %s tidak dapat disortir", $status);
                })
                ->extraAttributes([
                    'style' => 'opacity:100%;', // Tambahkan kelas kustom di sini
                ])
                ->visible(in_array($this->record->transaction->status, [TransactionStatus::CANCELED, TransactionStatus::RETURNED]))
                ->color('danger'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->disabled(fn() => in_array($this->record->transaction->status, [TransactionStatus::CANCELED, TransactionStatus::RETURNED]))
            ->schema([
                Section::make()
                    ->columns([
                        'lg' => 3
                    ])
                    ->schema([
                        TextInput::make('transaction.number')
                            ->label('Nomer Transaksi')
                            ->disabled(),
                        DateTimePicker::make('created_at')
                            ->label('Waktu Transaksi')
                            ->native(false)
                            ->displayFormat('j M Y, H.i')
                            ->seconds(false)
                            ->disabled(),
                        TextInput::make('qty_in_kg')
                            ->label('Berat Awal')
                            ->suffix('Kg')
                            ->disabled(),
                        TextInput::make('transaction.customer.name')
                            ->label('Pelanggan')
                            ->afterStateHydrated(fn($state, Set $set) => !$state ? $set('transaction.customer.name', '-') : $state)
                            ->disabled(),
                        TextInput::make('transaction.customer_category.name')
                            ->label('Kategori Pelanggan')
                            ->afterStateHydrated(fn($state, Set $set) => !$state ? $set('transaction.customer.name', '-') : $state)
                            ->disabled(),
                        TextInput::make('is_sorted')
                            ->label('Status Pemilahan')
                            ->formatStateUsing(fn($state) => $state === 0 ? 'Belum Disortir' : 'Sudah Disortir')
                            ->disabled(),
                    ]),
                Section::make()
                    ->schema([
                        Repeater::make('sorted_wastes')
                            ->columns(2)
                            ->label('Pilah Sampah')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->required()
                            ->live()
                            ->addActionLabel('Tambah Sampah Pilahan')
                            ->rules([
                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    $totalQty = 0;
                                    $items = $value;
                                    $maxQty = $get('qty_in_kg');
                                    $sortedQty = 0;

                                    if (is_array($items)) {
                                        foreach ($items as $item) {
                                            $sortedQty = (float) str_replace(',', '.', $item['qty_in_kg']) ?? '0';
                                            $totalQty += $sortedQty;
                                        }
                                    }

                                    if ($maxQty < $totalQty) {
                                        Notification::make()
                                            ->title('Gagal Menyimpan Data')
                                            ->body('Berat sampah sortiran melebihi berat awal')
                                            ->icon('heroicon-o-x-circle')
                                            ->danger()
                                            ->send();
                                        $fail("Berat sampah yang disortir melebihi berat awal");
                                    }
                                }
                            ])
                            ->schema([
                                Select::make('waste_id')
                                    ->label('Sampah')
                                    ->required()
                                    ->distinct()
                                    ->validationMessages([
                                        'distinct' => 'Jenis sampah yang sama sudah dipilih. Silakan pilih yang lain.',
                                    ])
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->searchable()
                                    ->options(
                                        Waste::query()->where('name', 'not like', '%' . 'campuran' . '%')->pluck('name', 'id')
                                    ),
                                TextInput::make('qty_in_kg')
                                    ->label('Berat')
                                    ->suffix('Kg')
                                    ->live()
                                    ->required()
                                    ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                                    ->dehydrateStateUsing(fn($state) => (float) str_replace(',', '.', $state))
                                    ->rules([
                                        // Berat harus lebih dari 0
                                        fn() =>
                                        function (string $attribute, $value, Closure $fail) {
                                            $qty = (float) str_replace(',', '.', $value);
                                            if ($qty <= 0.0) {
                                                Notification::make()->title('Gagal Menyimpan Data')
                                                    ->body("Berat sampah harus lebih dari 0")
                                                    ->danger()
                                                    ->send();
                                                $fail("Berat harus lebih dari 0");
                                            }
                                        },
                                    ]),
                            ])
                    ]),
            ])
            ->statePath('data');
    }

    private static function updateTotalQty(Get $get, Set $set): void
    {
        $total = 0;
        $items = $get('sorted_wastes');
        $maxQty = $get('qty_in_kg');
        $sortedQty = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                $sortedQty = (float) str_replace(',', '.', $item['qty_in_kg']) ?? '0';
                $total += $sortedQty;
            }
        }

        if ($maxQty < $sortedQty) {
            Notification::make()
                ->title('Gagal Menyimpan Data')
                ->body('Berat sampah pilahan melebihi sampah campuran awal')
                ->icon('heroicon-o-x-circle')
                ->error()
                ->send();
            return;
        }

        $set('sorted_qty', $total);
    }

    protected function getSubmitAction()
    {
        return Actions\Action::make('save')
            ->label('Simpan')
            ->hidden()
            ->submit('save');
    }

    protected function getFormActions()
    {
        if (in_array($this->record->transaction->status, [TransactionStatus::CANCELED, TransactionStatus::RETURNED])) {
            return [
                Actions\Action::make('cancel')
                    ->label('Kembali')
                    ->outlined()
                    ->url($this->getResource()::getUrl('index')),
            ];
        }

        return [
            Actions\Action::make('save')
                ->label('Simpan')
                ->submit('save'),
            Actions\Action::make('cancel')
                ->label('Batal')
                ->outlined()
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $transactionWaste = $this->record;


        DB::transaction(function () use ($data, $transactionWaste) {

            $movementType = $transactionWaste->is_sorted ? MovementType::SORTINGADJUST : MovementType::SORTINGIN;

            // Kurangi stok sampah campuran ketika pertama kali dipilah
            if (!$transactionWaste->is_sorted) {
                $this->stockMovementChange(
                    $transactionWaste->waste_id,
                    -$transactionWaste->qty_in_kg,
                    MovementType::SORTINGOUT,
                    $transactionWaste->transaction_id,
                    'Disortir dari transaksi' . $transactionWaste->transaction->number
                );
            }

            $oldQuantities = $transactionWaste->sortedWastes()->pluck('qty_in_kg', 'waste_id');
            $newQuantities = collect($data['sorted_wastes'])->pluck('qty_in_kg', 'waste_id');
            $allWasteIds = $oldQuantities->keys()->merge($newQuantities->keys())->unique();

            foreach ($allWasteIds as $wasteId) {
                $oldQty = $oldQuantities->get($wasteId, 0);
                $newQty = $newQuantities->get($wasteId, 0);
                $change = $newQty - $oldQty;

                // Hanya catat jika ada perubahan
                if ($change != 0) {
                    $this->stockMovementChange(
                        $wasteId,
                        $change, // Bisa positif (menambah) atau negatif (mengurangi)
                        $movementType,
                        $transactionWaste->transaction_id,
                        'Hasil sortir dari transaksi ' . $transactionWaste->transaction->number
                    );
                }
            }

            // Hapus semua sampah yang telah disortir, kemudian simpan ulang
            $transactionWaste->sortedWastes()->delete();
            foreach ($data['sorted_wastes'] as $item) {
                TransactionWaste::create([
                    'waste_id'       => $item['waste_id'],
                    'qty_in_kg'      => $item['qty_in_kg'],
                    'unit_price'     => 0,
                    'sub_total_price' => 0,
                    'is_sorted'      => false,
                    'transaction_id' => $transactionWaste->transaction->id,
                    'sorted_from_id' => $transactionWaste->id,
                ]);
            }

            // Update sampah campuran sudah disortir
            if (!$transactionWaste->is_sorted) {
                $transactionWaste->update(['is_sorted' => true]);
            }
        });

        Notification::make()
            ->success()
            ->title('Berhasil Disimpan')
            ->body('Data hasil sortir sampah telah berhasil disimpan.')
            ->send();

        $this->redirect(MixedWasteResource::getUrl('index'));
    }

    protected function stockMovementChange(int $wasteId, float $quantityChange, MovementType $type, ?int $transactionId = null, ?string $description = null): void
    {
        if ($quantityChange == 0) {
            return;
        }

        $waste = Waste::findOrFail($wasteId);
        $currentStock = $waste->stock_in_kg;
        $newStock = $currentStock + $quantityChange;
        // dd($waste, $quantityChange);

        StockMovement::create([
            'waste_id' => $wasteId,
            'type' => $type,
            'before_movement_kg' => $currentStock,
            'quantity_change_kg' => $quantityChange,
            'current_stock_after_movement_kg' => $newStock,
            'carbon_footprint_change_kg_co2e' => $quantityChange * $waste->category->emission_factor,
            'description' => $description,
            'transaction_id' => $transactionId,
            'user_id' => Auth::id(),
        ]);

        $waste->update(['stock_in_kg' => $newStock]);
    }
}
