<flux:main class="min-h-screen bg-white dark:bg-zinc-800">
    <div class="p-6 space-y-6">

        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold">Manage Corrections</h1>
            <flux:button size="sm" wire:click="create">
                New Correction
            </flux:button>
        </div>

        <flux:table>
            <flux:columns>
                <flux:column>Pattern</flux:column>
                <flux:column>Keywords</flux:column>
                <flux:column>Priority</flux:column>
                <flux:column>Active</flux:column>
                <flux:column>
                    <div class="w-full flex justify-end">
                        Actions
                    </div>
                </flux:column>
            </flux:columns>

            <flux:rows>
                @forelse($corrections as $c)
                    <flux:row :key="$c->id">
                        <flux:cell>{{ $c->question_pattern }}</flux:cell>
                        <flux:cell>{{ $c->keywords->pluck('name')->join(', ') }}</flux:cell>
                        <flux:cell>{{ $c->priority }}</flux:cell>
                        <flux:cell>
                            @if($c->active)
                                <flux:badge size="sm" color="green" inset="top bottom">Yes</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc" inset="top bottom">No</flux:badge>
                            @endif
                        </flux:cell>
                        <flux:cell class="flex justify-end space-x-2">
                            <flux:dropdown x-data align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item wire:click="edit({{ $c->id }})">
                                        Edit
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="delete({{ $c->id }})" onclick="confirm('Delete this correction?') || event.stopImmediatePropagation()">
                                        Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="5" class="text-center text-gray-500 dark:text-gray-400">
                            No corrections found.
                        </flux:cell>
                    </flux:row>
                @endforelse
            </flux:rows>
        </flux:table>

        <!-- Modal -->
        <flux:modal
            name="correction-modal"
            wire:model="showModal"
            class="md:w-96 space-y-6"
            @close="resetModalState"
        >
            <flux:heading size="lg">
                {{ $modalMode === 'create' ? 'New Correction' : 'Edit Correction' }}
            </flux:heading>

            <flux:field>
                <flux:label>Question Pattern</flux:label>
                <flux:input
                    id="question_pattern"
                    type="text"
                    wire:model.defer="question_pattern"
                    class="w-full"
                />
                <flux:error name="question_pattern" />
            </flux:field>

            <flux:field>
                <flux:label class="flex justify-between items-center">
                    <span>Keywords</span>
                    <a
                        x-cloak
                        wire:show="!showAddNew"
                        wire:click.prevent="showAddNew = true"
                        class="text-sm text-pvox-orange hover:text-pvox-link-dark cursor-pointer"
                    >
                        Add new
                    </a>
                </flux:label>
                <flux:select
                    id="selectedKeywords"
                    wire:model.defer="selectedKeywords"
                    multiple
                    :filter="false"
                    variant="listbox"
                    class="w-full"
                >
                    @foreach($allKeywords as $keyword)
                        <flux:option value="{{ $keyword->id }}">
                            {{ $keyword->name }}
                        </flux:option>
                    @endforeach
                </flux:select>
                <flux:error name="selectedKeywords" />
            </flux:field>

            <div wire:show="showAddNew" x-cloak>
                <flux:field>
                    <flux:label>Add New Keyword</flux:label>
                    <div class="flex space-x-2">
                        <flux:input
                            id="newKeyword"
                            type="text"
                            wire:model.defer="newKeyword"
                            placeholder="Enter keyword…"
                            class="flex-1"
                            x-effect="
          if ($wire.showAddNew) {
            setTimeout(() => {
              const el = document.getElementById('newKeyword');
              if (el) el.focus();
            }, 50);
          }
        "
                        />
                        <flux:button
                            size="sm"
                            variant="outline"
                            type="button"
                            wire:click="addKeyword"
                        >
                            Add
                        </flux:button>
                    </div>
                    <flux:error name="newKeyword" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Answer Text</flux:label>
                <flux:textarea
                    id="answer_text"
                    wire:model.defer="answer_text"
                    class="w-full"
                    rows="4"
                />
                <flux:error name="answer_text" />
            </flux:field>

            <div class="space-y-1">
                <flux:field>
                    <flux:label>Priority</flux:label>
                </flux:field>
                <div class="flex items-center justify-between w-full gap-4">
                    <flux:input
                        id="priority"
                        type="number"
                        wire:model.defer="priority"
                        class="w-[8ch]"
                    />
                    <div class="flex items-center space-x-2">
                        <flux:label for="active">Active</flux:label>
                        <flux:checkbox id="active" wire:model.defer="active" />
                    </div>
                </div>
                <flux:error name="priority" />
                <flux:error name="active" />
            </div>

            <div class="flex justify-end space-x-2">
                <flux:button variant="outline" wire:click="$set('showModal', false)">
                    Cancel
                </flux:button>
                <flux:button wire:click="save">
                    Save
                </flux:button>
            </div>
        </flux:modal>

    </div>
</flux:main>
