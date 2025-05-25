<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

new class extends Component {
    public ?string $accessToken = null;
    public ?string $refreshToken = null;
    public ?string $expiresAt = null;
    public ?string $message = 'Hello, this is a test message.';

    public function mount()
    {
        $user = Auth::user();
        try {
            $socialuser= $user->getSocialProviderUser('tesla');


            //  dd($socialuser);
            if (!$user) {
                $this->message = 'No user is currently authenticated.';
                return;
            }
            if (!$socialuser) {
                $this->message = 'No Tesla social provider found for the user.';
                return;
            }
            // Initialize access token and expiration
            $this->accessToken = $socialuser->token
                ? $socialuser->token
                : 'No access token available';
            $this->refreshToken = $socialuser->refresh_token
                ? $socialuser->refresh_token
                : 'No refresh token available';
            $this->expiresAt = $socialuser->token_expires_at
                ? $socialuser->token_expires_at->toDateTimeString()
                : 'Unknown expiration time';
            $this->message = 'Successfully initialized token information.';     

            //$socialuser->refreshToken($refreshToken = null);

            var_dump($socialuser);
            
        } catch (\Exception $e) {
            // Fallback to stored tokens
            $this->accessToken = $user->access_token ?? 'No access token';
            $this->expiresAt = $user->token_expires_at ?? 'Unknown';
            $this->message = 'Could not fetch latest token from Socialite.';
            var_dump($e->getMessage());
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
                Tesla Refresh Token:
            </div>
            <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                   wire:model="refreshToken" readonly />
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