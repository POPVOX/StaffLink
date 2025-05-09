<?php namespace App\Services;

namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openapi.key'));
    }

    public function generateEmbedding(string $text): array
    {
        return $this->client
            ->embeddings()
            ->create([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ])->embeddings;
    }

    public function getEmbeddingVector(string $text): array
    {
        $resp = $this->client
            ->embeddings()
            ->create([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ]);

        return $resp->embeddings[0]->embedding ?? [];
    }

    public function getChatResponse(array $messages): string
    {
        $response = $this->client->chat()->create([
            'model' => 'chatgpt-4o-latest',
            'messages' => $messages,
            'temperature' => 0.3, // Lowered to reduce creativity
            'top_p' => 0.9, // Lowered to make the output more focused
        ]);

        return $response->choices[0]->message->content ?? 'Error: No response';
    }
}
