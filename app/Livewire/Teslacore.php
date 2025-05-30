<?php
// This file is part of the TeslaCore package.
//
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

use Laravel\Socialite\Facades\Socialite;

function boot(): void
{
    Http::globalOptions([
        'verify' => false, // Disable SSL verification
    ]);
}
class TeslaCore extends Component
{

    protected $baseUrl = 'https://localhost:4443/api/1';

    public function httpfetchVehicles($vehicle): bool
    {
        $token = $this->getfreshToken();
        if (!$token)
            return false;

        $response = Http::withToken($token)->get("{$this->baseUrl}/vehicles");

        if ($response->successful()) {
            $this->vehicles = $response->json('response', []);
        } else {
            //$this->showError("Failed to fetch vehicles: " . $response->json('error', 'Unknown error'));
            return false;
        }
        foreach ($this->vehicles as &$vehicle) {
            $vehicleTag = $vehicle['vin'] ?? null;
            if ($vehicleTag) {
                $vehicleDataResponse = Http::withToken($token)
                    ->get("{$this->baseUrl}/vehicles/{$vehicleTag}/vehicle_data");

                if ($vehicleDataResponse->successful()) {
                    $vehicle['data'] = $vehicleDataResponse->json('response', []);
                } else {
                    //$vehicle['data'] = ['error' => $vehicleDataResponse->json('error', 'Unknown error')];
                    return false;
                }
            }
        }
        return $this->vehicles;
    }
    public function fetchVehicles(): array | bool
    {
        $token = $this->getfreshToken();
        if (!$token)
            return false;

        $ch = curl_init("{$this->baseUrl}/vehicles");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($response, true);


        if ($httpCode >= 200 && $httpCode < 300) {
            $this->vehicles = $response['response'] ?? [];
        } else {
            //$this->showError("Failed to fetch vehicles: " . ($response['error'] ?? 'Unknown error'));
            return false;
        }


        curl_close($ch);

        foreach ($this->vehicles as &$vehicle) {
            $vehicleTag = $vehicle['vin'] ?? null;
            if ($vehicleTag) {

                // $vehicleDataResponse = Http::withToken($token)->withOptions(['verify' => false,])
                //     ->get("{$this->baseUrl}/vehicles/{$vehicleTag}/vehicle_data");

                $ch = curl_init("{$this->baseUrl}/vehicles/{$vehicleTag}/vehicle_data");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $response = json_decode($response, true);


                if ($httpCode >= 200 && $httpCode < 300) {
                    $vehicle['data'] = $response['response'] ?? [];
                } else {
                    //$vehicle['data'] = ['error' => $vehicleDataResponse->json('error', 'Unknown error')];
                    return false;
                }
            }
        }
        return $this->vehicles;
    }

    public function httpsendCommand($vehicleId, $command)
    {

        $token = $this->getfreshToken();
        $response = Http::withToken($token)
            ->withOptions(['verify' => false])
            ->post("{$this->baseUrl}/vehicles/{$vehicleId}/{$command}");

        if ($response->successful()) {
            //$this->showSuccess("Command '{$command}' sent successfully!");
            // Refresh vehicle data after command
            //$this->fetchVehicles();
            return true;
        } else {
            //$this->showError("Error sending command: " . $response->json('reason', 'Unknown error'));
            return false;
        }
    }

    public function sendCommand($vehicleId, $command)
    {
        $token = $this->getfreshToken();
        if (!$token)
            return false;


        $url = "{$this->baseUrl}/vehicles/{$vehicleId}/{$command}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            //$this->showSuccess("Command '{$command}' sent successfully!");
            // Refresh vehicle data after command
            //$this->fetchVehicles();
            return true;
        } else {
            $responseBody = json_decode($response, true);
            $errorReason = $responseBody['reason'] ?? 'Unknown error';
            //$this->showError("Error sending command: " . $errorReason);
            throw new \Exception("Error sending command: " . $errorReason);
            return false;
        }
    }


    public function getfreshToken(): mixed
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
            return null;
        }

    }
}
