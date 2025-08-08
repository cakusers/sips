<x-filament-panels::page>
    <form wire:submit='save'>
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap items-center gap-4 justify-start">
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
        </div>
    </form>

</x-filament-panels::page>
