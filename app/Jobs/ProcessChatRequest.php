<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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
        // Prepare the message text to send to the Hugging Face model
        $query = $this->message->message;

        // Call the Hugging Face model to get a response
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.huggingface.token')
        ])->post('https://api-inference.huggingface.co/models/TinyLlama/TinyLlama-1.1B-Chat-v1.0', [
                    'inputs' => $query,
                ]);

        Log::info($response->json());

        // Parse the AI response
        $responseText = $response->json()[0]['generated_text'] ?? 'No response available';

        // Store the system's response as a message
        $this->message->conversation->messages()->create([
            'sender' => 'system',
            'message' => $responseText,
            'message_type' => 'text',
            'responded_by' => 'system',
        ]);

        // Optionally, mark the conversation as closed if the response is sufficient
        $this->message->conversation->update(['status' => 'closed']);

    }
}
