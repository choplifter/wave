<?php

use App\Models\TeslaApiTransaction;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

new class extends Component 
{
    public $transactions;

    public function mount()
    {
        $this->refreshTransactions();
    }

    public function refreshTransactions()
    {
        $this->transactions = TeslaApiTransaction::with('user')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();
    }

    public function deleteAll()
    {
        TeslaApiTransaction::truncate();
        $this->refreshTransactions();
    }
}
?>

<div>
    <h2 class="text-xl font-bold mb-4 flex items-center justify-between">
        Tesla API Transactions
        <button 
            wire:click="deleteAll"
            class="bg-red-600 text-black px-3 py-1 rounded text-xs hover:bg-red-700"
            onclick="return confirm('Delete all transactions?')"
        >
            Delete All
        </button>
    </h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 text-xs">
            <thead>
                <tr>
                    <th class="px-2 py-1 border">ID</th>
                    <th class="px-2 py-1 border">User</th>
                    <th class="px-2 py-1 border">Method</th>
                    <th class="px-2 py-1 border">Path</th>
                    <th class="px-2 py-1 border">Status</th>
                    <th class="px-2 py-1 border">Request</th>
                    <th class="px-2 py-1 border">Response</th>
                    <th class="px-2 py-1 border">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $txn)
                    <tr>
                        <td class="px-2 py-1 border">{{ $txn->id }}</td>
                        <td class="px-2 py-1 border">{{ $txn->user->name ?? 'N/A' }}</td>
                        <td class="px-2 py-1 border">{{ $txn->method }}</td>
                        <td class="px-2 py-1 border">{{ $txn->path }}</td>
                        <td class="px-2 py-1 border">{{ $txn->status }}</td>
                        <td class="px-2 py-1 border">
                            <button 
                                class="text-blue-600 underline focus:outline-none"
                                onclick="toggleJson('req-{{ $txn->id }}')"
                                type="button"
                            >
                                {{ Str::limit($txn->request_body, 80) }}
                            </button>
                            <div id="req-{{ $txn->id }}" class="hidden bg-gray-100 p-2 mt-1 rounded text-left overflow-x-auto">
                                <pre class="whitespace-pre-wrap break-all text-xs">{{ json_encode(json_decode($txn->request_body), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $txn->request_body }}</pre>
                            </div>
                        </td>
                        <td class="px-2 py-1 border">
                            <button 
                                class="text-blue-600 underline focus:outline-none"
                                onclick="toggleJson('res-{{ $txn->id }}')"
                                type="button"
                            >
                                {{ Str::limit($txn->response_body, 80) }}
                            </button>
                            <div id="res-{{ $txn->id }}" class="hidden bg-gray-100 p-2 mt-1 rounded text-left overflow-x-auto">
                                <pre class="whitespace-pre-wrap break-all text-xs">{{ json_encode(json_decode($txn->response_body), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $txn->response_body }}</pre>
                            </div>
                        </td>
                        <td class="px-2 py-1 border">{{ $txn->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <script>
        function toggleJson(id) {
            var el = document.getElementById(id);
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }
    </script>
</div>