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
        // Prepare the message text to send to the Hugging Face model
        $query = $this->message->message;

        // Call the Hugging Face model to get a response
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.huggingface.token')
        ])->post('https://api-inference.huggingface.co/models/your_model', [
                    'inputs' => $query,
                ]);

        // Parse the AI response
        $responseText = $response->json()[0]['generated_text'] ?? 'No response available';

        // Store the system's response as a message
        $this->message->conversation->messages()->create([
            'sender' => 'system',
            'message' => $responseText,
            'message_type' => 'text'
        ]);

        // Optionally, mark the conversation as closed if the response is sufficient
        $this->message->conversation->update(['status' => 'closed']);

    }
}
