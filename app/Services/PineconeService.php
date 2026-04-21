<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PineconeService
{
    public function upsertVector(string $id, array $vector, array $metadata = []): Response
    {
        return $this->request()->post('/vectors/upsert', [
            'vectors' => [[
                'id' => $id,
                'values' => $this->normalizeVector($vector),
                'metadata' => $metadata,
            ]],
        ])->throw();
    }

    public function queryVector(array $vector, int $topK = 5): Response
    {
        return $this->request()->post('/query', [
            'vector' => $vector,
            'topK' => $topK,
            'includeMetadata' => true,
        ])->throw();
    }

    public function storeChunks(array $chunks, string $documentId, array $metadata = [])
    {
        $openAIService = app(OpenAIService::class);
        $batch = [];

        foreach ($chunks as $index => $chunk) {
            $vector = $openAIService->getEmbeddingVector($chunk);
            $vector = $this->normalizeVector($vector);

            if (! is_array($vector) || empty($vector) || ! is_numeric($vector[0])) {
                continue;
            }

            $batch[] = [
                'id' => "{$documentId}_chunk-{$index}",
                'values' => $vector,
                'metadata' => array_merge($metadata, ['text' => $chunk, 'document_id' => $documentId]),
            ];
        }

        if (! empty($batch)) {
            return $this->request()->post('/vectors/upsert', [
                'vectors' => $batch,
            ])->throw();
        }

        return false;
    }

    public function retrieveRelevantChunks(string $query, int $topK = 3): array
    {
        $openAIService = app(OpenAIService::class);
        $vector = $this->normalizeVector($openAIService->getEmbeddingVector($query));

        if ($vector === []) {
            return [];
        }

        $results = $this->queryVector($vector, $topK)->json();

        return collect($results['matches'] ?? [])
            ->map(fn ($match) => $match['metadata']['text'] ?? '')
            ->filter()
            ->toArray();
    }

    public function listAllVectorIds(?string $prefix = null): array
    {
        $ids = [];
        $paginationToken = null;

        do {
            $response = $this->request()->get('/vectors/list', array_filter([
                'prefix' => $prefix,
                'paginationToken' => $paginationToken,
            ], fn ($value) => filled($value)))->throw()->json();

            foreach ($response['vectors'] ?? [] as $vector) {
                if (filled($vector['id'] ?? null)) {
                    $ids[] = $vector['id'];
                }
            }

            $paginationToken = $response['pagination']['next'] ?? null;
        } while (filled($paginationToken));

        return $ids;
    }

    public function fetchVector(string $id): array
    {
        return $this->request()
            ->get('/vectors/fetch', ['ids' => $id])
            ->throw()
            ->json("vectors.{$id}", []);
    }

    public function fetchVectors(array $ids): array
    {
        $vectors = [];

        foreach ($ids as $id) {
            $vectors[$id] = $this->fetchVector($id);
        }

        return $vectors;
    }

    public function deleteVectors(array $ids): int
    {
        $ids = array_values(array_filter($ids));

        if ($ids === []) {
            return 0;
        }

        $deleted = 0;

        foreach (array_chunk($ids, 100) as $chunk) {
            $this->request()->post('/vectors/delete', [
                'ids' => array_values($chunk),
            ])->throw();

            $deleted += count($chunk);
        }

        return $deleted;
    }

    public function deleteByPrefix(string $prefix): int
    {
        return $this->deleteVectors($this->listAllVectorIds($prefix));
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.pinecone.index_host'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Api-Key' => (string) config('services.pinecone.api_key'),
            ]);
    }

    protected function normalizeVector(array $vector): array
    {
        if ($vector === []) {
            return [];
        }

        if (is_numeric($vector[0] ?? null)) {
            return array_map('floatval', $vector);
        }

        $embedding = $vector[0]->embedding ?? $vector[0]['embedding'] ?? [];

        if (! is_array($embedding)) {
            return [];
        }

        return array_map('floatval', array_values($embedding));
    }
}
