<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ExpertController extends Controller
{
    /**
     * Create a new response to a query by expert
     * 
     * @param \Illuminate\Http\Request $request
     * @return MessageResource|mixed|\Illuminate\Http\JsonResponse
     */
    public function respondToQuery(MessageRequest $request)
    {
        // Validate the request
        $validated = $request->validated();

        // Retrieve the conversation
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Ensure the conversation is still open
        if ($conversation->status === 'closed') {
            return response()->json(['error' => 'This conversation is already closed.'], 400);
        }

        // Save the expert's response
        $message = $conversation->messages()->create([
            'sender' => 'system',
            'message' => $validated['message'],
            'message_type' => 'text',
            'responded_by' => 'expert',
            'expert_id' => $validated['expert_id'],
        ]);

        // Optionally mark the conversation as closed
        $conversation->update(['status' => 'closed']);

        // Return the response message
        return new MessageResource($message);
    }
}
