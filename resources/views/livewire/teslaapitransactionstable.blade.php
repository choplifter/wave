<?php

use App\Models\TeslaApiTransaction;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

new class extends Component 
{
    public  $transactions;
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function mount()
    {
        $this->transactions = TeslaApiTransaction::with(relations: 'user')
            ->latest()
            ->take(50)
            ->get();
           
    }
}
?>

<div>
    <h2 class="text-xl font-bold mb-4">Tesla API Transactions</h2>
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
                        <td class="px-2 py-1 border">{{ Str::limit($txn->request_body, 40) }}</td>
                        <td class="px-2 py-1 border">{{ Str::limit($txn->response_body, 40) }}</td>
                        <td class="px-2 py-1 border">{{ $txn->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>