<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Volt\Component;
use App\Livewire\TeslaCore;

new class extends Component {
    public ?string $accessToken = null;
    public ?string $message = null;
    public ?string $time = null;
    public ?string $messageType = null;
    public array $vehicles = [];


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

        $this->accessToken = (new TeslaCore())->getfreshToken();
        $this->message = 'Token retrieved successfully.';
    }

    public function httpsendCommand($vehicleId, $command)
    {
        $url = "https://owner-api.teslamotors.com/api/1/vehicles/{$vehicleId}/{$command}";
        $response = (new TeslaCore())->sendCommand($url, $this->accessToken);

        if ($response) {
            $this->showSuccess("Command '{$command}' sent successfully to vehicle {$vehicleId}.");
        } else {
            $this->showError("Failed to send command '{$command}' to vehicle {$vehicleId}: " );
        }
    }
    public function sendCommand($vehicleId, $command)
    {
        if (!$this->accessToken) {
            $this->showError('Access token is not available.');
            return;
        }

        try {
            $this->httpsendCommand($vehicleId, $command);
        } catch (Exception $e) {
            $this->showError('An error occurred while sending the command: ' . $e->getMessage());
        }
    }
    public function fetchVehicles()
    {
        if (!$this->accessToken) {
            $this->showError('Access token is not available.');
            return;
        }

        try {
            (new TeslaCore())->getVehicles($this->vehicles);
            $this->showSuccess('Vehicles fetched successfully.');
        } catch (Exception $e) {
            $this->showError('An error occurred while fetching vehicles: ' . $e->getMessage());
        }
    
    }

    private function showSuccess($message)
    {
        $this->message = $message;
        $this->messageType = 'success';
    }

    private function showError($message)
    {
        $this->message = $message;
        $this->messageType = 'error';
    }
}; ?>


<div>
    @if (Auth::user()->isAdmin())
        <div>
            @if ($message)
                <div
                    class="{{ $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' }} px-4 py-3 rounded relative mb-4">
                    {{ $message }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @if (count($vehicles) > 0)
                    @foreach ($vehicles as $vehicle)
                        <div
                            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                            <h2 class="font-semibold">{{ $vehicle['vin'] }}</h2>
                            <p>Status: <span>{{ $vehicle['state'] }}</span></p>
                            <p>Battery Level: {{ $vehicle['data']['charge_state']['battery_level'] ?? 'N/A' }}%</p>
                            <p>Charge Amps: {{ $vehicle['data']['charge_state']['charge_amps'] ?? 'N/A' }}</p>
                            <p>Charging State: {{ $vehicle['data']['charge_state']['charging_state'] ?? 'N/A' }}</p>

                            <div class="flex flex-wrap gap-2 mt-4">
                                <button wire:click="sendCommand('{{ $vehicle['vin'] }}', 'wake_up')"
                                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Wake Up
                                </button>

                                <button wire:click="sendCommand('{{ $vehicle['vin'] }}', 'command/door_unlock')"
                                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                    Unlock
                                </button>

                                <button wire:click="sendCommand('{{ $vehicle['vin'] }}', 'command/door_lock')"
                                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                    Lock
                                </button>

                                <button wire:click="sendCommand('{{ $vehicle['vin'] }}', 'command/honk_horn')"
                                    class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                    Honk
                                </button>

                                <button wire:click="sendCommand('{{ $vehicle['vin'] }}', 'command/flash_lights')"
                                    class="px-3 py-1 bg-purple-500 text-white rounded hover:bg-purple-600">
                                    Flash Lights
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>No vehicle data available.</p>
                @endif
            </div>

            <div class="mt-4">
                <button wire:click="fetchVehicles" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Refresh Vehicles
                </button>
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
