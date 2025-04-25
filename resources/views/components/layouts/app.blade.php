<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    @vite('resources/css/app.css')
    <title>{{ $title ?? 'StaffLink Chatbot' }}</title>
    @livewireStyles
    @fluxStyles

    <script>
        if (!localStorage.getItem('flux.appearance')) {
            localStorage.setItem('flux.appearance', 'dark');
        }
    </script>
</head>
<body class="bg-white dark:bg-zinc-800 antialiased min-h-screen theme-accent-blue">
<script>
    (function(){
        const url = new URL(window.location.href);
        if (url.searchParams.has('message')) {
            url.searchParams.delete('message');
            window.history.replaceState(null, '', url.pathname + url.search + url.hash);
        }
    })();
</script>
<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="#" class="flex items-center px-2">
        <img src="{{ asset('images/logo.svg') }}" alt="StaffUp Logo" class="w-[160px] h-auto dark:hidden" />
        <img src="{{ asset('images/logo-white.svg') }}" alt="StaffUp Logo" class="w-[160px] h-auto hidden dark:flex" />
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.item
            as="a"
            icon="home"
            wire:navigate
            href="/"
        >
            Home
        </flux:navlist.item>

        <flux:navlist.item
            as="a"
            icon="book-open"
            wire:navigate
            href="/resources"
        >
            Resources
        </flux:navlist.item>

        <flux:navlist.item
            as="a"
            icon="question-mark-circle"
            wire:navigate
            href="/faq"
        >
            FAQ
        </flux:navlist.item>

        <flux:navlist.item
            as="a"
            icon="information-circle"
            wire:navigate
            href="/about"
        >
            About
        </flux:navlist.item>

        <flux:navlist.item
            as="a"
            icon="key"
            wire:navigate
            href="/privacy"
        >
            Privacy
        </flux:navlist.item>
    </flux:navlist>
    <flux:spacer />

    <flux:navlist variant="outline">
        <div class="relative">
            <flux:dropdown x-data align="end">
                <flux:navlist.item as="button" icon="paint-brush" class="w-full">
                    Appearance
                </flux:navlist.item>
                <flux:menu>
                    <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Light</flux:menu.item>
                    <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Dark</flux:menu.item>
                    <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">System</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
{{--        <flux:navlist.item icon="information-circle" href="#">Help</flux:navlist.item>--}}
        <flux:separator class="my-2" variant="subtle" />

        <div class="px-2 text-xs text-gray-600 dark:text-gray-400 text-center">
            &copy; {{ now()->year }} <flux:link href="https://popvox.org/" class="text-pvox-link-dark">POPVOX Foundation</flux:link>
        </div>
    </flux:navlist>
</flux:sidebar>

<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
</flux:header>
{{ $slot }}
<flux:toast position="top right" />
@livewireScripts
@fluxScripts
</body>
</html>
