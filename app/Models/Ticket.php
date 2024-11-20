<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'agent_id',
    ];

    // Người tạo ticket
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Agent được assign
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Labels liên quan
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'label_ticket');
    }

    // Categories liên quan
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_ticket');
    }
}
