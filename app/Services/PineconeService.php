<?php

namespace App\Services;

use Probots\Pinecone\Client as PineconeClient;

class PineconeService
{
    protected PineconeClient $pinecone;

    public function __construct()
    {
        $this->pinecone = new PineconeClient(config('services.pinecone.api_key'));
        $this->pinecone->setIndexHost(config('services.pinecone.index_host'));
    }

    public function upsertVector(string $id, array $vector, array $metadata = [])
    {
        return $this->pinecone->data()->vectors()->upsert([
            [
                'id' => $id,
                'values' => $vector,
                'metadata' => $metadata
            ]
        ]);
    }

    public function queryVector(array $vector, int $topK = 5)
    {
        return $this->pinecone->data()->vectors()->query([
            'vector' => $vector,
            'topK' => $topK,
            'includeMetadata' => true
        ]);
    }

    public function storeChunks(array $chunks, string $documentId)
    {
        $openAIService = app(OpenAIService::class);
        $batch = [];

        foreach ($chunks as $index => $chunk) {
            $vector = $openAIService->generateEmbedding($chunk);

            // ✅ Ensure vector is valid before adding to batch
            if (!is_array($vector) || empty($vector) || !is_numeric($vector[0])) {
                continue; // Skip invalid embeddings
            }

            $batch[] = [
                'id' => "{$documentId}_chunk-{$index}",
                'values' => array_map('floatval', $vector), // ✅ Ensure all values are floats
                'metadata' => ['text' => $chunk, 'document_id' => $documentId]
            ];
        }

        if (!empty($batch)) {
            return $this->pinecone->data()->vectors()->upsert($batch);
        }

        return false;
    }

    public function retrieveRelevantChunks(string $query, int $topK = 3): array
    {
        $openAIService = app(OpenAIService::class);
        $embeddings = $openAIService->generateEmbedding($query);

        $results = $this->pinecone->data()->vectors()->query(
            vector: array_values($embeddings[0]->embedding),
            topK: 5,
            includeMetadata: true
        )->json();

        return collect($results['matches'] ?? [])
            ->map(fn($match) => $match['metadata']['text'] ?? '')
            ->filter()
            ->toArray();
    }
}
