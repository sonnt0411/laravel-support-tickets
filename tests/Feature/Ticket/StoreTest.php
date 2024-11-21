<?php

namespace Tests\Feature\Ticket;

use App\Mail\TicketCreated;
use App\Models\Category;
use App\Models\Label;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_create_ticket_with_valid_data()
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user']);
        $categories = Category::factory()->count(2)->create();
        $labels = Label::factory()->count(3)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'This is a sample description for the ticket.',
            'priority' => 'high',
            'categoryIds' => $categories->pluck('id')->toArray(),
            'labelIds' => $labels->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'title' => 'Sample Ticket',
                     'description' => 'This is a sample description for the ticket.',
                     'priority' => 'high',
                     'status' => 'open',
                     'user_id' => $user->id,
                 ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Sample Ticket',
            'description' => 'This is a sample description for the ticket.',
            'priority' => 'high',
            'status' => 'open',
            'user_id' => $user->id,
        ]);

        $ticket = Ticket::find($response['id']);
        $this->assertNotNull($ticket);
        $this->assertEquals(2, $ticket->categories()->count());
        $this->assertEquals(3, $ticket->labels()->count());

        Mail::assertSent(TicketCreated::class, function ($mail) use ($ticket) {
            return $mail->hasTo(config('mail.admin_email')) &&
                   $mail->ticket->id === $ticket->id;
        });
    }

    /** @test */
    public function unauthenticated_user_cannot_create_ticket()
    {
        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'This is a sample description for the ticket.',
            'priority' => 'high',
            'categoryIds' => [1, 2],
            'labelIds' => [1, 2, 3],
        ];

        $this->postJson('/api/tickets', $payload)
             ->assertStatus(401);
    }

    /** @test */
    public function it_requires_all_fields()
    {
        $user = User::factory()->create(['role' => 'user']);

        $payload = [];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'title',
                     'description',
                     'priority',
                     'categoryIds',
                     'labelIds',
                 ]);
    }

    /** @test */
    public function it_validates_priority_field()
    {
        $user = User::factory()->create(['role' => 'user']);
        $categories = Category::factory()->count(1)->create();
        $labels = Label::factory()->count(1)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'Valid description.',
            'priority' => 'urgent', // Invalid priority
            'categoryIds' => $categories->pluck('id')->toArray(),
            'labelIds' => $labels->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function it_validates_category_ids_exist()
    {
        $user = User::factory()->create(['role' => 'user']);
        $labels = Label::factory()->count(1)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'Valid description.',
            'priority' => 'low',
            'categoryIds' => [999], // Non-existent category
            'labelIds' => $labels->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['categoryIds.0']);
    }

    /** @test */
    public function it_validates_label_ids_exist()
    {
        $user = User::factory()->create(['role' => 'user']);
        $categories = Category::factory()->count(1)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'Valid description.',
            'priority' => 'medium',
            'categoryIds' => $categories->pluck('id')->toArray(),
            'labelIds' => [999], // Non-existent label
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['labelIds.0']);
    }

    /** @test */
    public function it_handles_missing_category_ids()
    {
        $user = User::factory()->create(['role' => 'user']);
        $labels = Label::factory()->count(1)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'Valid description.',
            'priority' => 'medium',
            // 'categoryIds' => [], // Missing categoryIds
            'labelIds' => $labels->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['categoryIds']);
    }

    /** @test */
    public function it_handles_missing_label_ids()
    {
        $user = User::factory()->create(['role' => 'user']);
        $categories = Category::factory()->count(1)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'Valid description.',
            'priority' => 'medium',
            'categoryIds' => $categories->pluck('id')->toArray(),
            // 'labelIds' => [], // Missing labelIds
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['labelIds']);
    }

    /** @test */
    public function it_sends_email_after_creating_ticket()
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user']);
        $categories = Category::factory()->count(1)->create();
        $labels = Label::factory()->count(1)->create();

        $payload = [
            'title' => 'Sample Ticket',
            'description' => 'This is a sample description for the ticket.',
            'priority' => 'high',
            'categoryIds' => $categories->pluck('id')->toArray(),
            'labelIds' => $labels->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(201);

        Mail::assertSent(TicketCreated::class, function ($mail) use ($user) {
            return $mail->hasTo(config('mail.admin_email')) &&
                   $mail->ticket->user_id === $user->id;
        });
    }

    /** @test */
    public function it_does_not_send_email_if_ticket_creation_fails()
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user']);
        // Assuming validation will fail due to missing fields
        $payload = [
            'title' => 'Incomplete Ticket',
            // 'description' => 'Missing description.',
            'priority' => 'high',
            'categoryIds' => [],
            'labelIds' => [],
        ];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tickets', $payload);

        $response->assertStatus(422);

        Mail::assertNothingSent();
    }
}
