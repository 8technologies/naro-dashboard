<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Jobs\ProcessChatRequest;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{

    /**
     * Get all conversations for a user
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getUserConversations(Request $request)
    {
        // Validate that the user_id is provided in the request
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Fetch all conversations for the user, including messages
        $conversations = Conversation::where('user_id', $validated['user_id'])
            ->with('messages') // Eager load the messages related to the conversation
            ->get();

        // Return the conversations as a collection of resources
        return ConversationResource::collection($conversations);
    }

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

    /**
     * Send a message in an existing conversation
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Conversation $conversation
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        // Validate the request body
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // Create the new message in the conversation
        $message = $conversation->messages()->create([
            'sender' => 'farmer', // Assuming 'farmer' is the sender
            'message' => $validated['message'],
            'message_type' => 'text', // Can be modified to handle different types (e.g., images, etc.)
        ]);

        // Return the newly created message as a resource
        return response()->json([
            'message' => 'Message sent successfully',
            'data' => new MessageResource($message),
        ], 201);
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
