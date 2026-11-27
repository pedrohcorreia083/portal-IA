<?php

namespace Tests\Feature;

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    private function login(string $email, string $password = 'password')
    {
        return $this->post('/login', ['email' => $email, 'password' => $password]);
    }

    public function test_inactive_user_cannot_keep_a_session(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->login($user->email)->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_user_reaches_the_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->login($user->email)->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_technical_role_is_recognised_as_technical(): void
    {
        $technician = User::factory()->create(['type' => 'technical']);
        $customer = User::factory()->create(['type' => 'user']);

        $this->assertTrue($technician->isTechnical());
        $this->assertFalse($customer->isTechnical());
    }

    public function test_regular_user_cannot_manage_users(): void
    {
        $user = User::factory()->create(['type' => 'user']);

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_can_render_the_users_and_service_orders_lists(): void
    {
        $admin = User::factory()->create(['type' => 'admin']);

        $order = ServiceOrder::create([
            'client_name' => 'Cliente teste', 'consultant_name' => 'Consultora',
            'client_code' => '123', 'origin' => 'SHOWROOM SP', 'order_date' => '2026-11-17',
        ]);
        $order->items()->create(['identified_product_name' => 'Anel', 'services' => ['B'], 'coating' => 'DO']);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()->assertSee($admin->email);

        $this->actingAs($admin)->get(route('service-orders.index'))
            ->assertOk()->assertSee($order->order_number);

        // O PDF deriva o tipo de garantia das peças, não mais da OS.
        $this->actingAs($admin)->get(route('service-orders.pdf', $order))->assertOk();
    }
}
