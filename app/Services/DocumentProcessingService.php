<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\DocumentChunkingService;
use App\Services\PineconeService;

class DocumentProcessingService
{
    protected DocumentChunkingService $chunkingService;
    protected PineconeService $pineconeService;

    public function __construct(DocumentChunkingService $chunkingService, PineconeService $pineconeService)
    {
        $this->chunkingService = $chunkingService;
        $this->pineconeService = $pineconeService;
    }

    public function extractGoogleDocText(string $url): string
    {
        if (preg_match('/docs\.google\.com\/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $documentId = $matches[1];
            $exportUrl = "https://docs.google.com/document/d/{$documentId}/export?format=txt";

            $response = Http::get($exportUrl);

            return $response->successful() ? $response->body() : "Error: Unable to fetch document.";
        }

        return "Error: Invalid Google Docs link.";
    }

    public function processGoogleDoc(string $url)
    {
        $text = $this->extractGoogleDocText($url);

        if (str_starts_with($text, "Error")) {
            return $text; // Handle errors gracefully
        }

        // Generate a unique document ID to avoid overwriting chunks
        $documentId = uniqid('doc_');

        // Chunk the document text
        $chunks = $this->chunkingService->splitText($text);

        // Store chunks in Pinecone with a unique document ID
        $this->pineconeService->storeChunks($chunks, $documentId);

        return "✅ Document processed successfully!";
    }

    public function processPlainText(string $text)
    {
        if (empty(trim($text))) {
            return "❌ The uploaded file is empty.";
        }

        // Generate a unique document ID
        $documentId = uniqid('doc_');

        // Chunk the text file content
        $chunks = $this->chunkingService->splitText($text);

        // Store chunks in Pinecone
        $this->pineconeService->storeChunks($chunks, $documentId);

        return "✅ Text file processed successfully!";
    }

}
