<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_staff_can_exchange_messages_only_on_their_own_order_conversation(): void
    {
        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Cliente da Conversa',
            'phone' => '+244900000001',
            'email' => 'chat-customer@example.test',
            'password' => 'password',
            'active' => true,
        ]);

        $otherCustomer = Customer::query()->create([
            'customer_type' => 'individual',
            'name' => 'Outro Cliente',
            'phone' => '+244900000002',
            'email' => 'other-customer@example.test',
            'password' => 'password',
            'active' => true,
        ]);

        $staff = User::query()->create([
            'name' => 'Atendimento GPM',
            'email' => 'support@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'seller', 'active' => true])->save();

        $order = Order::query()->create([
            'order_number' => 'GPM-CHAT-0001',
            'customer_id' => $customer->id,
            'customer_type' => 'company',
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'subtotal' => 1000,
            'total' => 1000,
            'currency' => 'AOA',
            'payment_status' => 'pending',
            'status' => 'confirmed',
            'stock_deducted' => false,
            'ordered_at' => now(),
            'confirmed_at' => now(),
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.orders.conversation', $order))
            ->assertRedirect();

        $conversation = Conversation::query()->where('order_id', $order->id)->firstOrFail();

        $this->actingAs($customer, 'customer')
            ->post(route('customer.conversations.messages.store', $conversation), [
                'body' => 'Gostaria de saber o estado da minha encomenda.',
            ])
            ->assertRedirect(route('customer.conversations.show', $conversation));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'customer',
            'customer_sender_id' => $customer->id,
        ]);

        $this->actingAs($staff, 'web')
            ->get(route('admin.conversations.show', $conversation))
            ->assertOk();

        $customerMessage = $conversation->messages()->where('sender_type', 'customer')->firstOrFail();
        $this->assertNotNull($customerMessage->fresh()->read_at);

        $this->actingAs($staff, 'web')
            ->post(route('admin.conversations.messages.store', $conversation), [
                'body' => 'A sua encomenda está em preparação.',
            ])
            ->assertRedirect(route('admin.conversations.show', $conversation));

        $this->actingAs($customer, 'customer')
            ->get(route('customer.conversations.show', $conversation))
            ->assertOk();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'staff',
            'user_sender_id' => $staff->id,
        ]);

        $this->actingAs($otherCustomer, 'customer')
            ->get(route('customer.conversations.show', $conversation))
            ->assertNotFound();
    }
}
