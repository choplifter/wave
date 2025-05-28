<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Volt\Component;

new class extends Component {
    public ?string $accessToken = null;

    public ?string $refreshToken = null;

    public ?string $expiresAt = null;

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
        $this->refreshToken();
    }
    public function refreshToken(): string
    {
        $user = Auth::user();
        try {
            $socialuser = $user->getSocialProviderUser('tesla');

            if (!$user) {
                $this->message = 'No user is currently authenticated.';

                return null;
            }
            if (!$socialuser) {
                $this->message = 'No Tesla social provider found for the user.';

                return null;
            }
            $this->time = now()->toDateTimeString();
            $this->message = 'Fetching latest token information...';

            // Check if token is expired or about to expire
            if ($socialuser->token_expires_at && now()->addMinutes(2)->gte($socialuser->token_expires_at)) {
                // Use Socialite's refreshToken method
                $provider = Socialite::driver('tesla');
                $token = $provider->refreshToken($socialuser->refresh_token);

                $this->accessToken = $token->token;
                $this->refreshToken = $token->refreshToken;
                $this->expiresAt = now()->addSeconds($token->expiresIn)->toDateTimeString();

                // Store new tokens in socialuser model
                $socialuser->token = $this->accessToken;
                $socialuser->refresh_token = $this->refreshToken;
                $socialuser->token_expires_at = $this->expiresAt;
                $socialuser->save();

                $this->message = 'Tesla token refreshed!';
            } else {
                $this->accessToken = $socialuser->token ?? 'No access token available';
                $this->refreshToken = $socialuser->refresh_token ?? 'No refresh token available';
                $this->expiresAt = $socialuser->token_expires_at ? $socialuser->token_expires_at->toDateTimeString() : 'Unknown expiration time';
                $this->message = 'Successfully initialized token information.';
            }
            return $this->accessToken;

        } catch (\Exception $e) {
            $this->accessToken = $user->access_token ?? 'No access token';
            $this->expiresAt = $user->token_expires_at ?? 'Unknown';
            $this->message = 'Could not fetch latest token from Socialite.';
        }

    }
}; ?>


<div>
    @if (Auth::user()->isAdmin())
        <div class="p-6 max-w-sm mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md space-y-4">
            <div class="text-center text-sm text-gray-500 mb-4">
                <span class="font-semibold">Admin Mode:</span> You can view and refresh Tesla tokens.
            </div>
            <input type="text" class="text-center text-sm text-red-500 w-full mb-2" wire:model="message" readonly />

            <div class="space-y-2">
                <div class="text-gray-700 dark:text-gray-200 font-semibold">Tesla Access Token:</div>
                <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                    wire:model="accessToken" readonly />
            </div>
            <div class="space-y-2">
                <div class="text-gray-700 dark:text-gray-200 font-semibold">Tesla Refresh Token:</div>
                <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                    wire:model="refreshToken" readonly />
            </div>
            <div class="space-y-2">
                <div class="text-gray-700 dark:text-gray-200 font-semibold">Expires At:</div>
                <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                    wire:model="expiresAt" readonly />
            </div>
            <div class="space-y-2">
                <div class="text-gray-700 dark:text-gray-200 font-semibold">Time of Fetch:</div>
                <input type="text" class="break-all bg-gray-100 dark:bg-gray-700 p-2 rounded text-xs w-full"
                    wire:model="time" readonly />
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
