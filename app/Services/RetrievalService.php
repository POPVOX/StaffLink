<?php

namespace App\Services;

use App\Models\Correction;
use App\Services\PineconeService;
use App\Services\OpenAIService;

class RetrievalService
{
    protected PineconeService $pineconeService;
    protected OpenAIService $openAI;

    public function __construct(PineconeService $pineconeService, OpenAIService $openAI)
    {
        $this->pineconeService = $pineconeService;
        $this->openAI          = $openAI;
    }

    /**
     * Look for a high-priority “source of truth” correction that matches
     * the query by embedding similarity, and only return it if similarity ≥ 0.75.
     */
    public function getCorrectionForQuery(string $query): ?Correction
    {
        // 1) only active corrections with a stored example_embedding
        $candidates = Correction::where('active', true)
            ->whereNotNull('example_embedding')
            ->orderByDesc('priority')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // 2) embed the incoming query
        $queryVec = $this->openAI->getEmbeddingVector($query);

        // 3) find the best match by cosine similarity; tie-break on priority
        $best    = null;
        $bestSim = 0.0;

        foreach ($candidates as $corr) {
            $example = $corr->example_embedding; // cast to array by your model
            if (! is_array($example) || empty($example)) {
                continue;
            }

            $sim = $this->cosineSimilarity($queryVec, $example);

            if (
                $best === null
                || $sim > $bestSim
                || ($sim === $bestSim && $corr->priority > $best->priority)
            ) {
                $bestSim = $sim;
                $best    = $corr;
            }
        }

        // 4) enforce a minimum similarity threshold
        return ($best && $bestSim >= 0.75) ? $best : null;
    }

    /**
     * Your existing RAG lookup against Pinecone.
     */
    public function retrieveContextForQuery(string $query, int $topK = 5): string
    {
        $chunks = $this->pineconeService->retrieveRelevantChunks($query, $topK);

        if (empty($chunks)) {
            return '';
        }

        return implode("\n", $chunks);
    }

    protected function dot(array $a, array $b): float
    {
        return array_sum(array_map(fn($x, $y) => $x * $y, $a, $b));
    }

    protected function magnitude(array $v): float
    {
        return sqrt(array_sum(array_map(fn($x) => $x * $x, $v)));
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $den = $this->magnitude($a) * $this->magnitude($b);
        return $den > 0 ? $this->dot($a, $b) / $den : 0;
    }
}
