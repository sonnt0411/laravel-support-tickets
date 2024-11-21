<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Ticket;
use App\Mail\TicketCreated;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::query();

        // Filter tickets based on user role
        if ($user->role === 'agent') {
            $query->where('agent_id', $user->id);
        } elseif ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply categories filter
        if ($request->filled('categories')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->whereIn('categories.id', $request->categories);
            });
        }

        $tickets = $query->with(['categories', 'labels', 'user', 'agent'])->get();

        return response()->json($tickets);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'categoryIds' => 'required|array',
            'categoryIds.*' => 'exists:categories,id',
            'labelIds' => 'required|array',
            'labelIds.*' => 'exists:labels,id',
        ]);

        DB::transaction(function () use ($validated, &$ticket) {
            $ticket = Ticket::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'user_id' => Auth::id(),
            ]);

            $ticket->categories()->sync($validated['categoryIds']);
            $ticket->labels()->sync($validated['labelIds']);
        });

        $adminEmail = config('mail.admin_email');
        Mail::to($adminEmail)->send(new TicketCreated($ticket));

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
