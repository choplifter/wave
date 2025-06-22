<?php
use function Laravel\Folio\{middleware, name};
use Laravel\livewire\Counter;
middleware('auth');
name('dashboard');
?>

<x-layouts.app>
    <div class="max-w-5xl lg:px-4 mx-auto">
        <h2 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }} 👋</h2>
    </div>
    <div class="grid grid-cols-1 gap-10 md:grid-cols-2  lg:px-6 lg:py-6 mt-8 space-y-8 space-x-6">
        @if (auth()->user()->hasRole('admin'))
            <div class="flex md:space-x-4 md:space-y-0 space-y-6 md:flex-row flex-col">
                <x-app.dashboard-card icon="phosphor-user" label="Users" value="143" change-direction="up"
                    change-percentage="25"></x-app.dashboard-card>
                <x-app.dashboard-card icon="phosphor-money" label="Sales" value="54" change-direction="down"
                    change-percentage="5"></x-app.dashboard-card>
                <x-app.dashboard-card icon="phosphor-arrows-clockwise" label="Refunds" value="3"
                    change-direction="up" change-percentage="10"></x-app.dashboard-card>
            </div>
        @endif


        <div class="w-full p-10 space-y-4 border border-black">
            @livewire('teslacommands')
        </div>

    </div>
</x-layouts.app>
