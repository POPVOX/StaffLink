<flux:card>
    <flux:heading size="lg">Upload Document to RAG System</flux:heading>

    <form wire:submit.prevent="processDocument" class="space-y-4">
        <flux:input wire:model.defer="url" placeholder="Paste Google Docs link here..." class="w-full" />
        <flux:input type="file" wire:model="document" accept=".txt" class="w-full" />
        <flux:button type="submit" icon="plus">Process Document</flux:button>
    </form>

    @if($message)
        <div class="mt-3 text-center text-sm font-semibold"
             x-data="{ show: true }" x-show="show"
             x-transition.opacity.scale.90.duration.300ms
             x-init="setTimeout(() => show = false, 5000)">
            {{ $message }}
        </div>
    @endif
</flux:card>
