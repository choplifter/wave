<?php

namespace App\Livewire;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

use Livewire\Component;

class Teslacore extends Component
{
   
    public function refreshToken(): string
    {
        $user = Auth::user();
        try {
            $socialuser = $user->getSocialProviderUser('tesla');

            if (!$user) {
                $this->message = 'No user is currently authenticated.';

                return '';
            }
            if (!$socialuser) {
                $this->message = 'No Tesla social provider found for the user.';

                return '';
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
            return '';
        }

    }
}
