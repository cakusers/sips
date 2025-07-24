<?php

namespace App\Filament\Resources\MixedWasteResource\Pages;

use Closure;
use Exception;
use App\Models\Waste;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Enums\MovementType;
use Filament\Actions\Action;
use App\Models\StockMovement;
use App\Enums\TransactionType;
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
    protected static ?string $title = 'Pilah Sampah Campuran';
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // Jika sudah dipilah tampilkan sampahnya
        if ($this->record->is_sorted) {
            $this->form->fill(
                $this->record->load(['transaction.customer', 'sortedWastes'])->toArray()
            );
        } else {
            $this->form->fill(
                $this->record->load('transaction.customer')->toArray()
            );
        }
    }

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'lg' => 5
                    ])
                    ->schema([
                        TextInput::make('transaction.number')
                            ->label('Nomer Transaksi')
                            ->disabled(),
                        TextInput::make('is_sorted')
                            ->label('Status Pemilahan')
                            ->formatStateUsing(fn($state) => $state === 0 ? 'Belum dipilah' : 'Sudah Dipilah')
                            ->disabled(),
                        TextInput::make('transaction.customer.name')
                            ->label('Pelanggan')
                            ->disabled(),
                        TextInput::make('qty_in_kg')
                            ->label('Berat')
                            ->suffix('Kg')
                            ->disabled(),
                        DateTimePicker::make('created_at')
                            ->label('Dilakukan pada')
                            ->native(false)
                            ->displayFormat('j F Y, H.i')
                            ->seconds(false)
                            ->disabled()
                    ]),
                Section::make()
                    ->schema([
                        Repeater::make('sorted_wastes')
                            ->columns(2)
                            ->label('Pilah Sampah')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->required()
                            ->schema([
                                Select::make('waste_id')
                                    ->label('Jenis Sampah')
                                    ->required()
                                    ->distinct()
                                    ->validationMessages([
                                        'distinct' => 'Jenis sampah yang sama sudah dipilih. Silakan pilih yang lain.',
                                    ])
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->options(
                                        Waste::query()->where('name', 'not like', '%' . 'campuran' . '%')->pluck('name', 'id')
                                    ),
                                TextInput::make('qty_in_kg')
                                    ->label('Berat')
                                    ->suffix('Kg')
                                    ->required()
                                    ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                                    ->dehydrateStateUsing(fn($state) => (float) str_replace(',', '.', $state))
                                    ->rules([
                                        fn() =>
                                        function (string $attribute, $value, Closure $fail) {
                                            $qty = (float) str_replace(',', '.', $value);
                                            if ($qty <= 0.0) {
                                                Notification::make()->title('Data tidak berhasil disimpan')->danger()->send();
                                                $fail("Berat harus lebih dari 0");
                                            }
                                        },
                                    ]),
                            ])
                    ])
            ])
            ->statePath('data');
    }

    protected function getFormActions()
    {
        return [
            Action::make('save')
                ->label('Simpan Hasil Sortir')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $transactionWaste = $this->record;


        DB::transaction(function () use ($data, $transactionWaste) {

            // Kurangi stok sampah campuran ketika pertama kali dipilah
            if (!$transactionWaste->is_sorted) {
                $this->stockMovementChange(
                    $transactionWaste->waste_id,
                    -$transactionWaste->qty_in_kg,
                    MovementType::SORTINGIN,
                    $transactionWaste->transaction_id,
                    'Telah disortir dari' . $transactionWaste->transaction->number
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
                        MovementType::SORTINGIN, // Tipe tetap SORTINGIN karena ini adalah hasil
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

        StockMovement::create([
            'waste_id' => $wasteId,
            'type' => $type,
            'before_movement_kg' => $currentStock,
            'quantity_change_kg' => $quantityChange,
            'current_stock_after_movement_kg' => $newStock,
            'description' => $description,
            'transaction_id' => $transactionId,
            'user_id' => Auth::id(),
        ]);

        $waste->update(['stock_in_kg' => $newStock]);
    }
}
