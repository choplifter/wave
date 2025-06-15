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
        // Get fresh Tesla token using Teslacore Livewire component
        $teslacore = new Teslacore();
        $token = $teslacore->getfreshToken();

        if (!$token) {
            return response()->json(['error' => 'Unable to retrieve Tesla access token'], 401);
        }

        $proxyUrl = "https://localhost:4443/api/1/{$any}";

        $response = Http::withOptions(['verify' => false])
            ->withToken($token)
            //->withHeaders($request->header())
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
}