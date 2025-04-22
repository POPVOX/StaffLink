<flux:main class="flex flex-col items-center justify-center min-h-screen bg-sky-50 dark:bg-sky-900">
    <div class="w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6">
        <!-- Chat Header -->
        <flux:heading size="xl" level="1" class="text-center text-sky-700 dark:text-sky-300 mt-4">
            <span id="greeting">Hello!</span>
        </flux:heading>
        <flux:subheading size="lg" class="text-center text-gray-600 dark:text-gray-400 mb-4">
            Welcome to the StaffUp chatbot
        </flux:subheading>
        <flux:separator variant="subtle" class="mb-4" />

        <!-- Chat Messages -->
        <div id="chatbox" class="flex-1 overflow-y-auto px-6 py-6 space-y-4 w-full max-w-4xl h-[550px] scroll-smooth"
             x-data
             x-init="$nextTick(() => $dispatch('scroll-to-bottom'))">

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
                        {{-- Message Bubble --}}
                        <div class="assistant-message-body mr-auto flex max-w-[75%] md:max-w-[65%]
                  flex-col gap-2 rounded-r-md rounded-tl-md bg-sky-100 p-4
                  text-sky-900 dark:bg-sky-900 dark:text-sky-100">

                            {{-- Header --}}
                            <span class="font-semibold text-sky-900 dark:text-sky-50">StaffUp Bot</span>

                            {{-- Actual Text --}}
                            <div class="assistant-text [&_*]:text-[15px] [&_*]:leading-[1.6]
                    [&_ul]:pl-5 [&_ul]:my-3 [&_ul]:list-disc [&_ul]:marker:text-sky-400
                    dark:[&_ul]:marker:text-sky-500 [&_li]:mb-1
                    [&_strong]:font-semibold [&_strong]:text-sky-800 dark:[&_strong]:text-sky-300">
                                {!! $message->content !!}
                            </div>

                            {{-- Footer: timestamp + dropdown --}}
                            <div class="flex items-center justify-between">
          <span
              class="timestamp ml-auto text-xs text-sky-600 dark:text-sky-400"
              data-utc="{{ $message->created_at->toIso8601String() }}"
          >
            {{ $message->created_at->format('h:i A') }}
          </span>

                                <flux:dropdown x-data align="end">
                                    {{-- trigger button --}}
                                    <flux:button
                                        as="button"
                                        variant="ghost"
                                        icon="ellipsis-vertical"
                                        class="size-6 p-1 text-sky-600 dark:text-sky-400"
                                    />
                                    <flux:menu>
                                        {{-- Report opens your existing modal --}}
                                        <flux:menu.item x-on:click="$flux.modal('feedback-modal').show()">
                                            Report issue
                                        </flux:menu.item>
                                        {{-- Copy the assistant-text --}}
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
                        <div class="ml-auto flex max-w-[80%] md:max-w-[75%] flex-col gap-2 rounded-l-lg rounded-tr-lg bg-sky-600 p-4 text-base text-white dark:bg-sky-500">
                            <div class="[&_*]:text-[15px] [&_*]:leading-[1.6]">
                                <p>{{ $message->content }}</p>
                            </div>
                            <span class="timestamp ml-auto text-xs text-sky-200" data-utc="{{ $message->created_at->toIso8601String() }}">
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
                    <flux:input wire:model.defer="message" placeholder="Type your message..." />

                    <flux:button type="submit" icon="plus" variant="primary">Send</flux:button>
                </flux:input.group>
            </form>
        </div>
    </div>

    <flux:modal name="feedback-modal" class="md:w-96 space-y-6">
        <!-- Modal Title (outside the form) -->
        <div class="space-y-1">
            <flux:heading size="lg">Report an issue</flux:heading>
            <flux:subheading>Let us know what happened or share suggestions.</flux:subheading>
        </div>

        <!-- Form fields -->
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
    window.updateGreeting = function() {
        const currentHour = new Date().getHours();
        let greeting = '';

        if (currentHour < 12) {
            greeting = 'Good morning!';
        } else if (currentHour < 18) {
            greeting = 'Good afternoon!';
        } else {
            greeting = 'Good evening!';
        }

        const greetingEl = document.getElementById('greeting');

        if (greetingEl) {
            greetingEl.textContent = greeting;
        }
    };

    window.updateTimestamps = function() {
        document.querySelectorAll('.timestamp').forEach(el => {
            const utcTime = el.getAttribute('data-utc');
            if (utcTime) {
                const date = new Date(utcTime);
                el.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        });
    }

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
</script>
@endscript
