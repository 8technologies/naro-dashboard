<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessChatRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if an expert has already responded
        $conversation = $this->message->conversation;
        $expertResponseExists = $conversation->messages()->where('responded_by', 'expert')->exists();

        if ($expertResponseExists) {
            // An expert has already provided a response, so do not send to AI
            return;
        }

        // Otherwise, process the AI response
        $query = $this->message->message;

        // Define the system prompt
        $systemPrompt = "You are an expert farmer specializing in groundnut cultivation. "
            . "You provide detailed, practical, and region-specific advice to farmers, focusing on the best practices for growing, "
            . "maintaining, and harvesting groundnuts. You are knowledgeable about groundnut varieties, optimal planting times, pest and disease control methods, "
            . "soil preparation, and yield improvement techniques. Be clear, concise, and friendly, while ensuring the advice is applicable to real-world farming scenarios.";

        // Combine the system prompt with the user's question
        $prompt = $systemPrompt . "\n\nFarmer's question: " . $query;

        try {
            // Make the HTTP POST request to OpenAI's v1/completions endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.key')
            ])->post('https://api.openai.com/v1/completions', [
                        'model' => 'ft:babbage-002:personal:naro:AIA9hcCa', // Your fine-tuned model
                        'prompt' => $prompt,
                        'max_tokens' => 150,  // Adjust token limit based on expected response length
                        'temperature' => 0.3, // Lower temperature for more precise and focused answers
                    ]);

            // Log the response for debugging
            Log::info($response->json());

            // Parse the AI response
            $responseText = $response->json()['choices'][0]['text'] ?? 'No response available';

        } catch (\Exception $e) {
            // Handle API error
            Log::error('Error calling OpenAI API: ' . $e->getMessage());
            $responseText = 'There was an error processing your request. Please try again later.';
        }

        // Store the system's response as a message
        $this->message->conversation->messages()->create([
            'sender' => 'system',
            'message' => trim($responseText),
            'message_type' => 'text',
            'responded_by' => 'system',
        ]);

        // Optionally, mark the conversation as closed if the response is sufficient
        $this->message->conversation->update(['status' => 'closed']);
    }
}
