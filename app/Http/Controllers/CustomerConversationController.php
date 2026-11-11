<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerConversationController extends Controller
{
    public function openForOrder(int $order): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $record = Order::query()
            ->where('customer_id', $customerId)
            ->findOrFail($order);

        $conversation = Conversation::query()->firstOrCreate(
            ['order_id' => $record->id],
            [
                'customer_id' => $customerId,
                'status' => 'open',
            ]
        );

        abort_unless((int) $conversation->customer_id === (int) $customerId, 404);

        return redirect()->route('customer.conversations.show', $conversation);
    }

    public function show(int $conversation): View
    {
        $customerId = Auth::guard('customer')->id();

        $record = Conversation::query()
            ->where('customer_id', $customerId)
            ->with(['order', 'messages.customerSender', 'messages.userSender'])
            ->findOrFail($conversation);

        $record->messages()
            ->where('sender_type', 'staff')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('customer.conversations.show', [
            'conversation' => $record->fresh(['order', 'messages.customerSender', 'messages.userSender']),
        ]);
    }

    public function send(Request $request, int $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($conversation, $validated): void {
            $record = Conversation::query()
                ->where('customer_id', Auth::guard('customer')->id())
                ->lockForUpdate()
                ->findOrFail($conversation);

            if ($record->status !== 'open') {
                throw ValidationException::withMessages([
                    'body' => 'Esta conversa está fechada. Aguarde a equipa GPM reabri-la.',
                ]);
            }

            $record->messages()->create([
                'sender_type' => 'customer',
                'customer_sender_id' => Auth::guard('customer')->id(),
                'user_sender_id' => null,
                'body' => trim($validated['body']),
            ]);

            $record->update(['last_message_at' => now()]);
        });

        return redirect()->route('customer.conversations.show', $conversation)
            ->with('success', 'Mensagem enviada à equipa GPM.');
    }
}
