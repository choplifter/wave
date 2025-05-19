<?php

use Livewire\Volt\Component;

new class extends Component {
    public int $count = 1;

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }
}; ?>

<div class="p-6 max-w-sm mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md space-y-4">
    <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-gray-100">
        {{ $count }}
    </h1>

    <div class="flex justify-center space-x-4">
        <button
            wire:click="decrement"
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition"
        >
            -
        </button>

        <button
            wire:click="increment"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition"
        >
            +
        </button>
    </div>
</div>