<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Models\Waste;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use App\Enums\MovementType;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\StockMovementResource;


class AdjustStockMovement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = StockMovementResource::class;
    // protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.adjust-stock';
    protected static ?string $title = 'Penyesuaian Stok';
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(StockMovement::class)
            ->schema([
                Grid::make([])
                    ->schema([
                        Section::make()
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                Select::make('waste_id')
                                    ->label('Jenis Sampah')
                                    ->required()
                                    ->relationship('waste', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, $state): void {
                                        if (!$state) {
                                            $set('stock', 0);
                                            return;
                                        }
                                        $waste = Waste::find($state);
                                        $set('stock', $this->strFormat($waste->stock_in_kg));
                                    }),
                                Select::make('adjustment_type')
                                    ->label('Tipe Penyesuaian')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'add' => '(+) Penambahan Stok',
                                        'subtract' => '(-) Pengurangan Stok',
                                    ]),
                                TextInput::make('quantity')
                                    ->label('Jumlah Penyesuaian')
                                    ->required()
                                    ->default(0)
                                    ->suffix('Kg'),
                                TextInput::make('stock')
                                    ->label('Stok Tersedia')
                                    ->readOnly()
                                    ->suffix('Kg')
                                    ->formatStateUsing(fn($state) => $this->strFormat($state))
                            ]),

                        Section::make('Deskripsi')
                            ->columnSpan(1)
                            ->schema([
                                Select::make('reason_type')
                                    ->label('Alasan Penyesuaian')
                                    ->options([
                                        'Koreksi Data' => 'Koreksi Data',
                                        'Hilang / Tidak Ditemukan' => 'Hilang / Tidak Ditemukan',
                                        'Rusak / Tidak Layak Jual' => 'Rusak / Tidak Layak Jual',
                                        'Digunakan Internal' => 'Digunakan Internal',
                                        'other' => 'Lain-lain',
                                    ])
                                    ->required()
                                    ->live(),

                                Textarea::make('reason_detail')
                                    ->label('Detail Alasan')
                                    ->placeholder('Jelaskan alasan penyesuaian secara detail...')
                                    ->hidden(fn(callable $get) => $get('reason_type') !== 'other') // Sembunyikan jika bukan 'Lainnya'
                                    ->required(fn(callable $get) => $get('reason_type') === 'other') // Wajib diisi jika 'Lainnya'
                                    ->rows(3)
                                    ->maxLength(500),
                            ]),
                    ]),

            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();

            DB::beginTransaction();

            $waste = Waste::find($data['waste_id']);
            if (!$waste) {
                throw ValidationException::withMessages(['waste_id' => 'Jenis sampah tidak ditemukan.']);
            }
            $currentQty = $waste->qty_in_kg;

            $quantity = $this->floatFormat($data['quantity']);
            if ($quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah harus lebih dari 0']);
            }
            $movementType = '';

            if ($data['adjustment_type'] === 'add') {
                $waste->stock_in_kg += $quantity;
                $movementType = MovementType::MANUALIN;
            } elseif ($data['adjustment_type'] === 'subtract') {
                if ($waste->stock_in_kg < $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'Jumlah pengurangan melebihi stok yang tersedia. Stok saat ini: ' . $waste->stock_in_kg . ' Kg']);
                }
                $waste->stock_in_kg -= $quantity;
                $quantity = -$quantity;
                $movementType = MovementType::MANUALOUT;
            }

            $waste->save();

            // Catat pergerakan di stock_movements
            StockMovement::create([
                'waste_id' => $data['waste_id'],
                'type' => $movementType,
                'before_movement_kg' => $currentQty,
                'quantity_change_kg' => $quantity,
                'current_stock_after_movement_kg' => $waste->stock_in_kg,
                'description' => $data['reason_type'] === 'other' ? $data['reason_detail'] : $data['reason_type'],
                'transaction_id' => null,
                'user_id' => Auth::user()->id,
            ]);

            DB::commit();

            Notification::make()
                ->title('Stok berhasil disesuaikan!')
                ->success()
                ->send();

            $this->form->fill();
            $this->redirect(StockMovementResource::getUrl('index'));
        } catch (ValidationException $e) {
            DB::rollBack();
            Notification::make()
                ->title('Gagal melakukan penyesuaian stok.')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Terjadi kesalahan!')
                ->body('Mohon coba lagi. Detail: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function strFormat(?float $number): string
    {
        return str_replace('.', ',', $number ?? 0);
    }

    private function floatFormat(string $number): float
    {
        return (float) str_replace(',', '.', $number ?? '0');
    }
}
