<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueryTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'topic'
    ];

    // QueryTopic belongs to a Conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
