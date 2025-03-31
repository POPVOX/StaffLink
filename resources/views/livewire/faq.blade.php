<flux:main class="min-h-screen bg-white dark:bg-zinc-800">
    <div class="mx-auto max-w-7xl px-6 py-4 sm:pt-6 lg:px-8 lg:py-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-5">
                    <h2 class="text-pretty text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Frequently asked questions</h2>
                    <p class="mt-4 text-pretty text-base/7 text-gray-600 dark:text-gray-400">Can’t find the answer you’re looking for? Reach out to our <a href="#" class="font-semibold text-pvox-orange hover:text-pvox-link-dark">support</a> team.</p>
                </div>
                <div class="mt-10 lg:col-span-7 lg:mt-0">
                    <dl class="space-y-10">
                        @foreach ($faqs as $faq)
                        <div>
                            <dt class="text-base/7 font-semibold text-gray-900 dark:text-white">{{ $faq['question'] }}</dt>
                            <dd class="mt-2 text-base/7 text-gray-600 dark:text-gray-400">{{ $faq['answer'] }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
</flux:main>
