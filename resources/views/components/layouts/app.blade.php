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

    <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo.png" name="StaffUp Portal" class="px-2 dark:hidden" />
    <flux:brand href="#" logo="https://fluxui.dev/img/demo/dark-mode-logo.png" name="StaffUp Portal" class="px-2 hidden dark:flex" />

    <!-- ✅ Updated Sidebar Navigation -->
    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" href="/">Home</flux:navlist.item>
        <flux:navlist.item icon="book-open" href="#">Resources</flux:navlist.item>
        <flux:navlist.item icon="question-mark-circle" href="#">FAQ</flux:navlist.item>
    </flux:navlist>

    <flux:spacer />

    <!-- Settings & Help Section -->
    <flux:navlist variant="outline">
        <flux:navlist.item icon="cog-6-tooth" href="#">Settings</flux:navlist.item>
        <flux:navlist.item icon="information-circle" href="#">Help</flux:navlist.item>
    </flux:navlist>
</flux:sidebar>

<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" align="start">
        <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                <flux:menu.radio>Truly Delta</flux:menu.radio>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:header>
{{ $slot }}
@livewireScripts
@fluxScripts
</body>
</html>
