<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Jobs\ProcessChatRequest;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConversationRequest $request)
    {
        // Validated data
        $validated = $request->validated();

        // Create a new conversation or find an existing one
        $conversation = Conversation::firstOrCreate([
            'user_id' => $validated['user_id'],
            'status' => 'open'
        ]);

        // Save the farmer's query in the database
        $message = $conversation->messages()->create([
            'sender' => 'farmer',
            'message' => $validated['message'],
            'message_type' => 'text'
        ]);

        // Dispatch the request to the queue for processing
        ProcessChatRequest::dispatch($message);

        // Return the conversation ID so the client can poll for updates
        return response()->json([
            'status' => 'processing',
            'message' => 'Your query is being processed.',
            'conversation_id' => $conversation->id
        ], 202);
    }

    public function getChatResponse($conversationId)
    {
        // Find the conversation and load the messages
        $conversation = Conversation::with('messages')->findOrFail($conversationId);

        // Check if the conversation is still processing
        if ($conversation->status === 'open') {
            return response()->json([
                'status' => 'processing',
                'message' => 'Your query is still being processed.',
            ], 200);
        }

        // Return the conversation with messages if processing is complete
        return new ConversationResource($conversation);
    }

}
