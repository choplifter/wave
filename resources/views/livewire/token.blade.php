<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

new class extends Component {
    public ?string $accessToken = null;
    public ?string $expiresAt = null;
    public ?string $message = 'Hello, this is a test message.';

    public function mount()
    {
        $user = Auth::user();

        try {
            // Attempt to get the latest token info from Socialite (if session available)
            $socialiteUser = Socialite::driver('tesla')->user();
            $this->accessToken = $socialiteUser->token ?? ($user->access_token ?? 'No access token');
            $this->expiresAt = $socialiteUser->expiresIn
                ? now()->addSeconds($socialiteUser->expiresIn)->toDateTimeString()
                : ($user->token_expires_at ?? 'Unknown');
        } catch (\Exception $e) {
            // Fallback to stored tokens
            $this->accessToken = $user->access_token ?? 'No access token';
            $this->expiresAt = $user->token_expires_at ?? 'Unknown';
            $this->message = 'Could not fetch latest token from Socialite.';
        }
    }
}; ?>

<div>
    <div class="p-6 max-w-sm mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md space-y-4">
        <input type="text" class="text-center text-sm text-red-500 w-full mb-2" wire:model="message" readonly />

        <div class="space-y-2">
            <div class="text-gray-700 dark:text-gray-200 font-semibold">
                Tesla Access Token:
            </div>
            <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                   wire:model="accessToken" readonly />
        </div>

        <div class="space-y-2">
            <div class="text-gray-700 dark:text-gray-200 font-semibold">
                Expires At:
            </div>
            <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                   wire:model="expiresAt" readonly />
        </div>
    </div>
</div>