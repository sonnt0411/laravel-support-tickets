<?php

namespace Tests\Feature\Ticket;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_admin_can_view_all_tickets()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/tickets');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_agent_can_view_assigned_tickets()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        Ticket::factory()->count(3)->create(['agent_id' => $agent->id]);
        Ticket::factory()->count(2)->create();

        $response = $this->actingAs($agent)->getJson('/api/tickets');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_user_can_view_own_tickets()
    {
        $user = User::factory()->create(['role' => 'user']);
        Ticket::factory()->count(4)->create(['user_id' => $user->id]);
        Ticket::factory()->count(1)->create();

        $response = $this->actingAs($user)->getJson('/api/tickets');

        $response->assertStatus(200);
        $response->assertJsonCount(4);
    }

    public function test_can_filter_tickets_by_status()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['status' => 'open']);
        Ticket::factory()->create(['status' => 'done']);

        $response = $this->actingAs($user)->getJson('/api/tickets?status=open');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_can_filter_tickets_by_priority()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['priority' => 'high']);
        Ticket::factory()->create(['priority' => 'low']);

        $response = $this->actingAs($user)->getJson('/api/tickets?priority=high');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_can_filter_tickets_by_category()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        Ticket::factory()->create()->categories()->attach($category->id);
        Ticket::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/tickets?category=' . $category->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }
}
