<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Mail\TicketCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'categoryIds' => 'array',
            'categoryIds.*' => 'exists:categories,id',
            'labelIds' => 'array',
            'labelIds.*' => 'exists:labels,id',
        ]);

        DB::transaction(function () use ($validated, &$ticket) {
            // Create the ticket
            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open',
            ]);

            // Attach categories if provided
            if (isset($validated['categoryIds'])) {
                $ticket->categories()->attach($validated['categoryIds']);
            }

            // Attach labels if provided
            if (isset($validated['labelIds'])) {
                $ticket->labels()->attach($validated['labelIds']);
            }
        });

        // Send email to admin if not in local environment
        if (!app()->isLocal()) {
            $adminEmail = config('app.admin_email');
            Mail::to($adminEmail)->send(new TicketCreated($ticket));
        }

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket' => $ticket->load(['categories', 'labels']),
        ], 201);
    }

    /**
     * Display a listing of the tickets with optional filters.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['categories', 'labels']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('id', $request->input('category'));
            });
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role == 'agent') {
            $query->where('agent_id', $user->id);
        } elseif ($user->role == 'user') {
            $query->where('user_id', $user->id);
        }

        $tickets = $query->paginate(10);

        return response()->json($tickets);
    }
}
