<flux:main class="flex flex-col items-center justify-center h-screen bg-sky-50 dark:bg-sky-900 px-2">
    <div class="flex flex-col w-full max-w-md sm:max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-lg p-2 sm:p-6 h-full">
        <flux:heading size="xl" level="1" class="text-center text-sky-700 dark:text-sky-300 mt-4">
            <span id="greeting">StaffLink chatbot</span>
        </flux:heading>
        <flux:subheading size="lg" class="text-center text-gray-600 dark:text-gray-400 mb-4">
            An experimental tool with information relevant to the work of junior staff in the U.S. Congress.        </flux:subheading>
        <flux:separator variant="subtle" class="mb-4" />

        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="relative flex items-center space-x-2 mb-4 p-4
         bg-yellow-50 text-yellow-800 rounded-lg border-l-4 border-yellow-400
         dark:bg-yellow-800/20 dark:text-yellow-200 dark:border-yellow-500"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5 flex-shrink-0"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
            </svg>

            <p class="text-sm leading-tight">
                This is an experimental chatbot trained on publicly available resources.
                Information received here is <strong>not official guidance</strong>.
            </p>

            <button
                @click="show = false"
                class="absolute top-2 right-3 text-xl leading-none text-yellow-800 dark:text-yellow-200 hover:opacity-75"
                aria-label="Dismiss"
            >
                &times;
            </button>
        </div>

        <!-- Chat Messages -->
        <div id="chatbox" class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-6 space-y-4 w-full scroll-smooth"
             x-data
             x-init="$nextTick(() => $dispatch('scrollToBottom'))">

            @foreach($messages as $message)
                <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)" x-show="show"
                     x-transition.opacity.scale.90.duration.300ms class="flex items-end gap-3"
                >
                    @if($message->role === 'assistant')
                        <span class="flex size-10 items-center justify-center overflow-hidden rounded-full border border-neutral-300 bg-neutral-50 text-sm font-bold tracking-wider text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z" />
</svg>

                        </span>
                        <div class="assistant-message-body mr-auto w-full sm:max-w-[75%] md:max-w-[65%] flex flex-col gap-2 rounded-r-md rounded-tl-md bg-sky-100 p-3 sm:p-4 text-sky-900 dark:bg-sky-900 dark:text-sky-100">

                            <span class="font-semibold text-sky-900 dark:text-sky-50">StaffLink Bot</span>

                            <div class="assistant-text [&_*]:text-[15px] [&_*]:leading-[1.6] [&_p]:mb-2
                    [&_ul]:pl-5 [&_ul]:my-3 [&_ul]:list-disc [&_ul]:marker:text-sky-400
                    dark:[&_ul]:marker:text-sky-500 [&_li]:mb-1
                    [&_strong]:font-semibold [&_strong]:text-sky-800 dark:[&_strong]:text-sky-300">
                                {!! $message->content !!}
                            </div>

                            <div class="flex items-center justify-between">
          <span
              class="timestamp ml-auto text-xs text-sky-600 dark:text-sky-400"
              data-utc="{{ $message->created_at->toIso8601ZuluString() }}"
          >
            {{ $message->created_at->format('h:i A') }}
          </span>

                                <flux:dropdown x-data align="end" position="top" class="ml-2">
                                    <flux:button
                                        as="button"
                                        variant="ghost"
                                        icon="ellipsis-vertical"
                                        class="size-6 p-1 text-sky-600 dark:text-sky-400"
                                    />
                                    <flux:menu>
                                        <flux:menu.item x-on:click="$flux.modal('feedback-modal').show()">
                                            Report issue
                                        </flux:menu.item>
                                        <flux:menu.item
                                            x-on:click="
                  const body = $event.target
                    .closest('.assistant-message-body')
                    .querySelector('.assistant-text');
                  navigator.clipboard.writeText(body.innerText);
                "
                                        >
                                            Copy text
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </div>
                    @else
                        <div class="ml-auto w-full sm:max-w-[80%] md:max-w-[75%] flex flex-col gap-2 rounded-l-lg rounded-tr-lg bg-sky-600 p-3 sm:p-4 text-base text-white dark:bg-sky-500">
                        <div class="[&_*]:text-[15px] [&_*]:leading-[1.6] [&_p]:mb-2">
                                <p>{{ $message->content }}</p>
                            </div>
                            <span class="timestamp ml-auto text-xs text-sky-200" data-utc="{{ $message->created_at->toIso8601ZuluString() }}">
    {{ $message->created_at->format('h:i A') }}
</span>
                        </div>
                        <span class="flex size-10 items-center justify-center overflow-hidden rounded-full border border-neutral-300 bg-neutral-50 text-sm font-bold tracking-wider text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
</svg>

                        </span>
                    @endif
                </div>
            @endforeach

            <!-- Typing Indicator -->
            <div x-show="$wire.botTyping" class="flex items-end gap-3">
                <span class="flex size-10 items-center justify-center overflow-hidden rounded-full border border-neutral-300 bg-neutral-50 text-sm font-bold tracking-wider text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z" />
</svg>
                        </span>
                <div class="flex gap-1">
                    <span class="size-2 rounded-full bg-sky-600 motion-safe:animate-[bounce_1s_ease-in-out_infinite] dark:bg-sky-300"></span>
                    <span class="size-2 rounded-full bg-sky-600 motion-safe:animate-[bounce_0.5s_ease-in-out_infinite] dark:bg-sky-300"></span>
                    <span class="size-2 rounded-full bg-sky-600 motion-safe:animate-[bounce_1s_ease-in-out_infinite] dark:bg-sky-300"></span>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-300 dark:border-gray-700 p-6">
            <form wire:submit.prevent="sendMessage" class="flex items-center gap-4">
                <flux:input.group>
                    <flux:input
                        x-ref="chatInput"
                        x-init="() => { if (@entangle('message')) $refs.chatInput.focus() }"
                        wire:model.defer="message"
                        placeholder="Enter your question..."
                    />
                    <flux:button type="submit" icon="plus" variant="primary">Send</flux:button>
                </flux:input.group>
            </form>
        </div>
    </div>

    <flux:modal name="feedback-modal" class="md:w-96 space-y-6">
        <div class="space-y-1">
            <flux:heading size="lg">Report an issue</flux:heading>
            <flux:subheading>Let us know what happened or share suggestions.</flux:subheading>
        </div>

        <form wire:submit.prevent="submitFeedback" class="space-y-4">
            @error('feedbackDetails')
            <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror

            <flux:input.group label="Details">
                <flux:textarea
                    wire:model.defer="feedbackDetails"
                    rows="4"
                    placeholder="Describe the message or behavior…"
                    required
                />
            </flux:input.group>

            <div class="flex gap-2">
                <flux:spacer/>

                <flux:button x-on:click="$flux.modal('feedback-modal').close()" type="button" variant="outline">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary" icon="paper-airplane">
                    Submit
                </flux:button>
            </div>
        </form>
    </flux:modal>
</flux:main>

@script
<script>
    window.updateTimestamps = function() {
        document.querySelectorAll('.timestamp').forEach(el => {
            const utcTime = el.getAttribute('data-utc');
            if (!utcTime) return;

            const normalized = utcTime.replace(/\+00:00$/, 'Z');

            const date = new Date(normalized);
            if (isNaN(date)) return; // sanity

            el.textContent = date.toLocaleTimeString([], {
                hour:   '2-digit',
                minute: '2-digit'
            });
        });
    };


    $wire.on('scrollToBottom', () => {
        window.updateTimestamps();
        window.updateGreeting();

        const container = document.getElementById('chatbox');
        setTimeout(() => {
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }, 150);
    });

    document.addEventListener('livewire:update', () => {
        window.updateTimestamps();
        window.updateGreeting();
    });

    document.addEventListener('livewire:load', () => {
        window.updateTimestamps();
        window.updateGreeting();
    });
</script>
@endscript
