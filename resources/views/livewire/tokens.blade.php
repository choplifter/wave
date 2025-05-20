<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public ?string $accessToken = null;
    public ?string $refreshToken = null;

    public function mount()
    {
        $user = Auth::user();
        $this->accessToken = $user->access_token ?? 'No access token';
        $this->refreshToken = $user->refresh_token ?? 'No refresh token';
    }
}; ?>

<div class="p-6 max-w-sm mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md space-y-4">
    <div class="space-y-2">
        <div class="text-gray-700 dark:text-gray-200 font-semibold">
            Access Token:
        </div>
        <div class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs">
            {{ $accessToken }}
        </div>
    </div>

    <div class="space-y-2">
        <div class="text-gray-700 dark:text-gray-200 font-semibold">
            Refresh Token:
        </div>
        <div class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs">
            {{ $refreshToken }}
        </div>
    </div>
</div>