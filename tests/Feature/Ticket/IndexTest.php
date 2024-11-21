<?php

namespace Tests\Feature\Ticket;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_all_tickets()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->count(5)->create();

        $this->actingAs($admin, 'sanctum')
             ->getJson('/api/tickets')
             ->assertStatus(200)
             ->assertJsonCount(5);
    }

    /** @test */
    public function agent_can_view_assigned_tickets()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        Ticket::factory()->count(3)->create(['agent_id' => $agent->id]);
        Ticket::factory()->count(2)->create();

        $this->actingAs($agent, 'sanctum')
             ->getJson('/api/tickets')
             ->assertStatus(200)
             ->assertJsonCount(3);
    }

    /** @test */
    public function user_can_view_own_tickets()
    {
        $user = User::factory()->create(['role' => 'user']);
        Ticket::factory()->count(4)->create(['user_id' => $user->id]);
        Ticket::factory()->count(1)->create();

        $this->actingAs($user, 'sanctum')
             ->getJson('/api/tickets')
             ->assertStatus(200)
             ->assertJsonCount(4);
    }

    /** @test */
    public function it_filters_tickets_by_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['status' => 'open']);
        Ticket::factory()->create(['status' => 'closed']);

        $this->actingAs($admin, 'sanctum')
             ->getJson('/api/tickets?status=open')
             ->assertStatus(200)
             ->assertJsonFragment(['status' => 'open'])
             ->assertJsonMissing(['status' => 'closed']);
    }

    /** @test */
    public function it_filters_tickets_by_priority()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['priority' => 'high']);
        Ticket::factory()->create(['priority' => 'low']);

        $this->actingAs($admin, 'sanctum')
             ->getJson('/api/tickets?priority=high')
             ->assertStatus(200)
             ->assertJsonFragment(['priority' => 'high'])
             ->assertJsonMissing(['priority' => 'low']);
    }

    /** @test */
    public function it_filters_tickets_by_categories()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $ticket1 = Ticket::factory()->create();
        $ticket1->categories()->attach($category1->id);

        $ticket2 = Ticket::factory()->create();
        $ticket2->categories()->attach($category2->id);

        $this->actingAs($admin, 'sanctum')
             ->getJson('/api/tickets?categories[]=' . $category1->id)
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $ticket1->id])
             ->assertJsonMissing(['id' => $ticket2->id]);
    }

    /** @test */
    public function it_applies_multiple_filters_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'high',
        ]);
        $ticket->categories()->attach($category->id);

        Ticket::factory()->create([
            'status' => 'closed',
            'priority' => 'low',
        ]);

        $this->actingAs($admin, 'sanctum')
             ->getJson('/api/tickets?status=open&priority=high&categories[]=' . $category->id)
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $ticket->id])
             ->assertJsonMissing(['status' => 'closed']);
    }
}
