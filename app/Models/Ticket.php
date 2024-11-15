<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    /**
     * The user who created the ticket.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The agent assigned to the ticket.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * The categories associated with the ticket.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_ticket');
    }

    /**
     * The labels associated with the ticket.
     */
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'label_ticket');
    }
}
