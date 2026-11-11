<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_form_stores_a_message(): void
    {
        $response = $this->from(route('home'))->post(route('contact.store'), [
            'name' => 'Empresa de Teste',
            'email' => 'contact@example.test',
            'phone' => '+244900000111',
            'subject' => 'Pedido de informação',
            'message' => 'Pretendemos receber uma proposta de fornecimento.',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Empresa de Teste',
            'email' => 'contact@example.test',
            'subject' => 'Pedido de informação',
            'message' => 'Pretendemos receber uma proposta de fornecimento.',
        ]);
    }

    public function test_authorized_staff_can_view_contact_messages(): void
    {
        ContactMessage::query()->create([
            'name' => 'Cliente de Contacto',
            'email' => 'visitor@example.test',
            'subject' => 'Consulta',
            'message' => 'Gostaria de falar com a GPM.',
        ]);

        $staff = User::query()->create([
            'name' => 'Atendimento GPM',
            'email' => 'contact-staff@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'seller', 'active' => true])->save();

        $this->actingAs($staff, 'web')
            ->get(route('admin.contacts.index'))
            ->assertOk()
            ->assertSee('Cliente de Contacto')
            ->assertSee('Gostaria de falar com a GPM.');
    }
}
