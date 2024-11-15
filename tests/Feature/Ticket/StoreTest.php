<?php

namespace Tests\Feature\Ticket;

use App\Models\User;
use App\Models\Category;
use App\Models\Label;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\TicketCreated;
use App\Models\Ticket;

class StoreTest extends TestCase
{
    public function test_store_ticket_successfully()
    {
        Mail::fake();
        config(['app.env' => 'testing']);
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => 'Test Ticket',
            'description' => 'This is a test ticket.',
            'priority' => 'high',
            'categoryIds' => Category::factory()->count(2)->create()->pluck('id')->toArray(),
            'labelIds' => Label::factory()->count(2)->create()->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'ticket' => [
                    'id',
                    'user_id',
                    'title',
                    'description',
                    'priority',
                    'status',
                    'categories',
                    'labels',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Test Ticket',
            'description' => 'This is a test ticket.',
            'priority' => 'high',
            'status' => 'open',
            'user_id' => $user->id,
        ]);

        $ticket = Ticket::find($response['ticket']['id']);

        // Check if categories are attached
        $this->assertEquals(2, $ticket->categories()->count());

        // Check if labels are attached
        $this->assertEquals(2, $ticket->labels()->count());

        Mail::assertSent(TicketCreated::class, function ($mail) use ($user) {
            return $mail->hasTo(config('app.admin_email'));
        });
    }

    public function test_store_ticket_validation_failure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => '',
            'description' => '',
            'priority' => '',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'description', 'priority']);
    }

    public function test_store_ticket_with_invalid_category_ids_and_label_ids()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => 'Test Ticket',
            'description' => 'This is a test ticket.',
            'priority' => 'medium',
            'categoryIds' => [999], // Assuming 999 does not exist
            'labelIds' => [999], // Assuming 999 does not exist
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['categoryIds.0', 'labelIds.0']);
    }

    public function test_store_ticket_with_invalid_priority()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => 'Invalid Priority Ticket',
            'description' => 'This ticket has an invalid priority.',
            'priority' => 'invalid_priority',
            'categoryIds' => Category::factory()->count(2)->create()->pluck('id')->toArray(),
            'labelIds' => Label::factory()->count(2)->create()->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_ticket_unauthenticated()
    {
        $data = [
            'title' => 'Unauthenticated Ticket',
            'description' => 'Should not be created.',
            'priority' => 'low',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(401);
    }
}
