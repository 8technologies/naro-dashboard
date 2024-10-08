<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status'
    ];

    // Conversation belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Conversation has many Messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Conversation can have many BotResponses
    public function botResponses()
    {
        return $this->hasMany(BotResponse::class);
    }

    // Conversation can have many QueryTopics (optional)
    public function queryTopics()
    {
        return $this->hasMany(QueryTopic::class);
    }

    // Conversation can have many FailedQueries (optional)
    public function failedQueries()
    {
        return $this->hasMany(FailedQuery::class);
    }
}
