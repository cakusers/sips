<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap items-center gap-4 justify-start">
            {{-- Tombol Simpan --}}
            <x-filament::button type="submit">
                Simpan Penyesuaian
            </x-filament::button>

            {{-- Tombol Batal / Kembali --}}
            <x-filament::button
                type="button"
                color="gray"
                tag="a"
                href="{{ \App\Filament\Resources\StockMovementResource::getUrl('index') }}"
            >
                Batal
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
