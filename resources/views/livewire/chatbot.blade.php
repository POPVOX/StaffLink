<flux:main class="flex flex-col items-center justify-center min-h-screen bg-sky-50 dark:bg-sky-900">
    <div class="w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6">
        <!-- Chat Header -->
        <flux:heading size="xl" level="1" class="text-center text-sky-700 dark:text-sky-300 mt-4">
            Good afternoon!
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
                        <img class="size-10 rounded-full object-cover" src="https://penguinui.s3.amazonaws.com/component-assets/avatar-8.webp" alt="bot-avatar" />
                        <div class="mr-auto flex max-w-[75%] md:max-w-[65%] flex-col gap-2 rounded-r-md rounded-tl-md bg-sky-100 p-4 text-sky-900 dark:bg-sky-900 dark:text-sky-100">
                            <span class="font-semibold text-sky-900 dark:text-sky-50">StaffUp Bot</span>
                            <div class="[&_*]:text-[15px] [&_*]:leading-[1.6]
                [&_ul]:pl-5 [&_ul]:my-3 [&_ul]:list-disc [&_ul]:marker:text-sky-400 dark:[&_ul]:marker:text-sky-500
                [&_li]:mb-1
                [&_strong]:font-semibold [&_strong]:text-sky-800 dark:[&_strong]:text-sky-300">
                                {!! $message->content !!}
                            </div>
                            <span class="timestamp ml-auto text-xs text-sky-600 dark:text-sky-400" data-utc="{{ $message->created_at->toIso8601String() }}">
    {{ $message->created_at->format('h:i A') }}
</span>
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
                        <span class="flex size-10 items-center justify-center overflow-hidden rounded-full border border-neutral-300 bg-neutral-50 text-sm font-bold tracking-wider text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">U</span>
                    @endif
                </div>
            @endforeach

            <!-- Typing Indicator -->
            <div x-show="$wire.botTyping" class="flex items-end gap-3">
                <img class="size-10 rounded-full object-cover" src="https://penguinui.s3.amazonaws.com/component-assets/avatar-8.webp" alt="avatar" />
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
</flux:main>

@script
<script>
    $wire.on('scrollToBottom', () => {
        document.querySelectorAll('.timestamp').forEach(el => {
            const utcTime = el.getAttribute('data-utc');
            if (utcTime) {
                const date = new Date(utcTime);
                el.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        });

        const container = document.getElementById('chatbox');
        setTimeout(() => {
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }, 150);
    });
</script>
@endscript
