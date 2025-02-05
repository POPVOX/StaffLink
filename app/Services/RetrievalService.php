<?php

namespace App\Services;

use App\Services\PineconeService;

class RetrievalService
{
    protected PineconeService $pineconeService;

    public function __construct(PineconeService $pineconeService)
    {
        $this->pineconeService = $pineconeService;
    }

    public function retrieveContextForQuery(string $query, int $topK = 5): string
    {
        $chunks = $this->pineconeService->retrieveRelevantChunks($query, $topK);

        if (empty($chunks)) {
            return "No relevant information found.";
        }

        return implode("\n", $chunks);
    }
}
