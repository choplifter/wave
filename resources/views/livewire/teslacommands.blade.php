<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Volt\Component;
use App\Livewire\TeslaCore;

new class extends Component {
    public ?string $accessToken = null;

    public ?string $message = null;

    public ?string $time = null;

    public function mount()
    {
        // Get timezone from cookie or request
        $userTimezone = $_COOKIE['user_timezone'] ?? ($_GET['tz'] ?? 'UTC');
        // Validate timezone (prevent injection)
        if (in_array($userTimezone, DateTimeZone::listIdentifiers())) {
            date_default_timezone_set($userTimezone);
        } else {
            date_default_timezone_set('UTC'); // Fallback
        }    
        

        $this->accessToken = (new TeslaCore)->getfreshToken();
        $this->message = 'Token retrieved successfully.';
    }
    
}; ?>


<div>
    @if (Auth::user()->isAdmin())
        <div class="p-6 max-w-sm mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md space-y-4">
            <div class="text-center text-sm text-gray-500 mb-4">
                <span class="font-semibold">Admin Mode:</span> You can view Tesla tokens.
            </div>
            <input type="text" class="text-center text-sm text-red-500 w-full mb-2" wire:model="message" readonly />

            <div class="space-y-2">
                <div class="text-gray-700 dark:text-gray-200 font-semibold">Tesla Access Token:</div>
                <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                    wire:model="accessToken" readonly />
            </div>

        </div>
    @else
        <div class="text-center text-sm text-gray-500 mb-4">
            You do not have permission to view this page.
        </div>
    @endif
    <script>
        // Get the browser's timezone (e.g., "America/New_York")
        const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        // Send to PHP via cookie or AJAX
        document.cookie = `user_timezone=${userTimezone}; path=/`;
    </script>
</div>
