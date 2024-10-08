<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'message',
        'error_reason'
    ];

    // FailedQuery belongs to a Conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
