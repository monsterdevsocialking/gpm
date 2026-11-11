<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminConversationController extends Controller
{
    public function index(): View
    {
        $conversations = $this->conversationList();

        return view('admin.conversations.index', [
            'conversations' => $conversations,
            'conversation' => null,
        ]);
    }

    public function show(int $conversation): View
    {
        $record = Conversation::query()
            ->with(['customer', 'order', 'messages.customerSender', 'messages.userSender'])
            ->findOrFail($conversation);

        $record->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversations = $this->conversationList();

        return view('admin.conversations.index', [
            'conversations' => $conversations,
            'conversation' => $record->fresh(['customer', 'order', 'messages.customerSender', 'messages.userSender']),
        ]);
    }

    public function send(Request $request, int $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($conversation, $validated): void {
            $record = Conversation::query()->lockForUpdate()->findOrFail($conversation);

            if ($record->status !== 'open') {
                throw ValidationException::withMessages([
                    'body' => 'Esta conversa está fechada. Reabra-a antes de responder.',
                ]);
            }

            $record->messages()->create([
                'sender_type' => 'staff',
                'customer_sender_id' => null,
                'user_sender_id' => Auth::guard('web')->id(),
                'body' => trim($validated['body']),
            ]);

            $record->update(['last_message_at' => now()]);
        });

        return redirect()->route('admin.conversations.show', $conversation)
            ->with('success', 'Mensagem enviada.');
    }

    public function toggleStatus(int $conversation): RedirectResponse
    {
        $record = Conversation::query()->findOrFail($conversation);
        $newStatus = $record->status === 'open' ? 'closed' : 'open';

        $record->update(['status' => $newStatus]);

        return redirect()->route('admin.conversations.show', $conversation)
            ->with('success', $newStatus === 'closed' ? 'Conversa fechada.' : 'Conversa reaberta.');
    }

    private function conversationList()
    {
        return Conversation::query()
            ->with(['customer', 'order'])
            ->withCount([
                'messages as unread_customer_messages_count' => fn ($query) => $query
                    ->where('sender_type', 'customer')
                    ->whereNull('read_at'),
            ])
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->orderByDesc('id')
            ->paginate(30);
    }
}
