<?php

namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $apiKey = (string) config('services.openai.key', config('services.openapi.key', ''));

        $this->client = OpenAI::client($apiKey);
    }

    public function generateEmbedding(string $text): array
    {
        return $this->client
            ->embeddings()
            ->create([
                'model' => $this->embeddingModel(),
                'input' => $text,
            ])->embeddings;
    }

    public function getEmbeddingVector(string $text): array
    {
        $resp = $this->client
            ->embeddings()
            ->create([
                'model' => $this->embeddingModel(),
                'input' => $text,
            ]);

        return $resp->embeddings[0]->embedding ?? [];
    }

    public function getChatResponse(array $messages): string
    {
        $response = $this->client->chat()->create([
            'model' => $this->chatModel(),
            'messages' => $messages,
            'temperature' => 0.3, // Lowered to reduce creativity
            'top_p' => 0.9, // Lowered to make the output more focused
        ]);

        return $response->choices[0]->message->content ?? 'Error: No response';
    }

    protected function chatModel(): string
    {
        return (string) config('services.openai.chat_model', 'gpt-4.1-mini');
    }

    protected function embeddingModel(): string
    {
        return (string) config('services.openai.embedding_model', 'text-embedding-ada-002');
    }
}
