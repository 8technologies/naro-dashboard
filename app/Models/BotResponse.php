<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'response_text'
    ];

    // BotResponse belongs to a Conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
