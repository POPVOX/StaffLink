<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    @vite('resources/css/app.css')
    <title>{{ $title ?? 'Staff Up Portal' }}</title>
    @livewireStyles
    @fluxStyles
</head>
<body class="bg-white dark:bg-zinc-800 antialiased min-h-screen theme-accent-blue">
<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="#" class="flex items-center px-2">
        <img src="{{ asset('images/logo.svg') }}" alt="StaffUp Logo" class="w-[160px] h-auto dark:hidden" />
        <img src="{{ asset('images/logo-white.svg') }}" alt="StaffUp Logo" class="w-[160px] h-auto hidden dark:flex" />
    </a>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" href="/">Home</flux:navlist.item>
        <flux:navlist.item icon="book-open" href="/resources">Resources</flux:navlist.item>
        <flux:navlist.item icon="information-circle" href="/about">About</flux:navlist.item>
        <flux:navlist.item icon="question-mark-circle" href="/faq">FAQ</flux:navlist.item>
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
    </flux:navlist>
</flux:sidebar>

<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
</flux:header>
{{ $slot }}
@livewireScripts
@fluxScripts
</body>
</html>
