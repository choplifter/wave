<?php
// filepath: c:\Users\akind\wave\app\Http\Controllers\TeslaApiProxyController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Livewire\Teslacore;
use App\Models\TeslaApiTransaction;
use Illuminate\Support\Facades\Auth;

class TeslaApiProxyController extends Controller
{
    public function forward(Request $request, $any)
    { 
        // Extract vehicleId if present in the path
        $vehicleId = null;
        $type = null;
        if (preg_match('#vehicles/([A-Za-z0-9]+)#', $any, $matches)) {
            $vehicleId = $matches[1];
        }

        if (strpos($any, 'command') !== false) {
            $type = 'command';
        }

        // If vehicleId found, wake up if offline
        if ($vehicleId && $type === 'command') {
            $wakeResult = $this->wakeUpIfOffline($vehicleId);
            // Optionally, you can log or handle $wakeResult as needed


            if ($wakeResult['state'] !== 'online') {
                return response()->json(['error' => 'Vehicle is offline and could not be woken up: '.$wakeResult['state']], 503);
            } 

            
        }

        
        // Get fresh Tesla token using Teslacore Livewire component
        $teslacore = new Teslacore();
        $token = $teslacore->getfreshToken();

        if (!$token) {
            return response()->json(['error' => 'Unable to retrieve Tesla access token'], 401);
        }
        // Filter out hop-by-hop headers that should not be forwarded
        
        $excludedHeaders = [
            'transfer-encoding',
            'content-length',
            'connection',
            'keep-alive',
            'proxy-authenticate',
            'proxy-authorization',
            'te',
            'trailer',
            'upgrade',
        ];
        $filteredHeaders = collect($request->header())->filter(function ($value, $key) use ($excludedHeaders) {
            return !in_array(strtolower($key), $excludedHeaders);
        })->toArray();

        $proxyUrl = "https://localhost:4443/api/1/{$any}";

        $response = Http::withOptions(['verify' => false])
            ->withToken($token)
            //->withHeaders($filteredHeaders)
            ->send($request->method(), $proxyUrl, [
                'query' => $request->query(),
                'body' => $request->getContent(),
            ]);

        // Log transaction
        TeslaApiTransaction::create([
            'user_id' => Auth::id(),
            'method' => $request->method(),
            'path' => $any,
            'status' => $response->status(),
            'request_body' => $request->getContent(),
            'response_body' => $response->body(),
        ]);



        return response($response->body(), $response->status())
            ->withHeaders($response->headers());
    }
    /**
     * Wake up the Tesla vehicle if it is offline.
     *
     * @param string $vehicleId
     * @return array ['woken_up' => bool, 'state' => string, 'response' => mixed]
     */
    public function wakeUpIfOffline($vehicleId)
    {
        $teslacore = new Teslacore();
        $token = $teslacore->getfreshToken();

        if (!$token) {
            return ['woken_up' => false, 'state' => 'unknown', 'response' => 'No token'];
        }

        // Get vehicle state
        $vehicleUrl = "https://localhost:4443/api/1/vehicles/{$vehicleId}";
        $vehicleResponse = Http::withOptions(['verify' => false])
            ->withToken($token)
            ->get($vehicleUrl);

            // log transaction
        TeslaApiTransaction::create([
            'user_id' => Auth::id(),
            'method' => 'GET',
            'path' => "vehicles/{$vehicleId}",
            'status' => $vehicleResponse->status(),
            'request_body' => null,
            'response_body' => $vehicleResponse->body(),
        ]);

        if (!$vehicleResponse->ok()) {
            return ['woken_up' => false, 'state' => 'unknown', 'response' => $vehicleResponse->body()];
        }

        $vehicleData = $vehicleResponse->json();
        $state = $vehicleData['response']['state'] ?? 'unknown';

        if ($state !== 'online') {
            // Wake up the vehicle
            $wakeUrl = "https://localhost:4443/api/1/vehicles/{$vehicleId}/wake_up";
            $wakeResponse = Http::withOptions(['verify' => false])
                ->withToken($token)
                ->post($wakeUrl);
                

                // log transaction
            TeslaApiTransaction::create([
                'user_id' => Auth::id(),
                'method' => 'POST',
                'path' => "vehicles/{$vehicleId}/wake_up",
                'status' => $wakeResponse->status(),
                'request_body' => null,
                'response_body' => $wakeResponse->body(),
            ]); 
            
            sleep(10);
            
            return [
                'woken_up' => true,
                'state' => $wakeResponse->ok() ? 'online' : 'unknown',
                'response' => $wakeResponse->json()
            ];
        }

        return [
            'woken_up' => false,
            'state' => 'online',
            'response' => 'Vehicle already online'
        ];
    }

}