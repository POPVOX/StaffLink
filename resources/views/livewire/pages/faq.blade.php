<flux:main class="min-h-screen bg-white dark:bg-zinc-800">
    <div class="mx-auto max-w-7xl px-6 py-4 sm:pt-6 lg:px-8 lg:py-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-5">
                <h2 class="text-pretty text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Frequently asked questions
                </h2>
                <p class="mt-4 text-pretty text-base/7 text-gray-600 dark:text-gray-400">
                    These are aggregations of the questions that are being asked of the StaffLink chatbot.
                </p>
                <p class="mt-4 text-pretty text-base/7 text-gray-600 dark:text-gray-400">
                    Our system groups questions by topic, generates a generic version of the question, and shares the number of times a related question was asked.
                </p>
                <p class="mt-4 text-pretty text-base/7 text-gray-600 dark:text-gray-400">
                    Use these as a way to see what colleagues are asking or as a jumping off point for your own!
                </p>
            </div>

            <div class="mt-10 lg:col-span-7 lg:mt-0">
                <dl class="space-y-10">
                    @foreach($faqs as $faq)
                        <div>
                            <dt class="text-base/7 font-semibold text-gray-900 dark:text-white">
                                <a
                                    wire:navigate
                                    href="/?message={{ urlencode($faq->representative_text) }}"
                                    class="hover:underline"
                                >
                                    {{ $faq->representative_text }}
                                </a>
                            </dt>
                            <dd class="mt-2 flex items-center space-x-2 text-base/7 text-gray-600 dark:text-gray-400">
                                {{-- Example “answer”: show how many times it was asked --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v10M16 7v10M3 12h18" />
                                </svg>
                                <span>{{ $faq->frequency }} have asked this</span>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</flux:main>
