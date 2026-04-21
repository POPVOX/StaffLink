<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

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

            return $response->successful() ? $response->body() : 'Error: Unable to fetch document.';
        }

        return 'Error: Invalid Google Docs link.';
    }

    public function processGoogleDoc(string $url, array $options = []): string
    {
        $text = $this->extractGoogleDocText($url);

        if (str_starts_with($text, 'Error')) {
            return $text; // Handle errors gracefully
        }

        return $this->ingestText($text, $options + [
            'source_url' => $url,
            'success_message' => '✅ Document processed successfully!',
        ])['message'];
    }

    public function processPlainText(string $text, array $options = []): string
    {
        $result = $this->ingestText($text, $options + [
            'success_message' => '✅ Text file processed successfully!',
        ]);

        return $result['message'];
    }

    public function processUploadedFile(string $path, string $originalName, array $options = []): string
    {
        $contents = Storage::get($path);
        $extension = Str::lower(pathinfo($originalName, PATHINFO_EXTENSION));
        $text = $extension === 'pdf'
            ? $this->extractPdfTextFromContents($contents)
            : $contents;

        $result = $this->ingestText($text, $options + [
            'source_url' => $originalName,
            'success_message' => "✅ File {$originalName} processed successfully!",
        ]);

        return $result['message'];
    }

    public function processPdfUrl(string $url, array $options = []): array
    {
        $response = Http::get($url);

        if (! $response->successful()) {
            return [
                'ok' => false,
                'message' => '❌ Unable to fetch the PDF.',
            ];
        }

        $text = $this->extractPdfTextFromContents($response->body());

        return $this->ingestText($text, $options + [
            'source_url' => $url,
            'success_message' => '✅ PDF processed successfully!',
        ]);
    }

    protected function ingestText(string $text, array $options = []): array
    {
        if (empty(trim($text))) {
            return [
                'ok' => false,
                'message' => '❌ The uploaded file is empty.',
            ];
        }

        $sourceKey = $this->normalizeSourceKey($options['source_key'] ?? null);
        $sourceLabel = trim((string) ($options['source_label'] ?? ''));
        $sourceUrl = trim((string) ($options['source_url'] ?? ''));
        $successMessage = (string) ($options['success_message'] ?? '✅ Document processed successfully!');
        $legacyMatches = array_values(array_filter(array_map(
            fn ($value) => trim((string) $value),
            $options['legacy_matches'] ?? []
        )));
        $dryRun = (bool) ($options['dry_run'] ?? false);

        $documentId = $sourceKey
            ? $this->documentIdForSourceKey($sourceKey)
            : uniqid('doc_');

        $chunks = $this->chunkingService->splitText($text);
        $replacedCurrent = 0;
        $replacedLegacy = 0;

        if ($sourceKey) {
            $replacedCurrent = count($this->pineconeService->listAllVectorIds("{$documentId}_chunk-"));
        }

        $legacyDocumentIds = $this->findLegacyDocumentIds($legacyMatches);

        if ($dryRun) {
            return [
                'ok' => true,
                'document_id' => $documentId,
                'chunk_count' => count($chunks),
                'replaced_current_vectors' => $replacedCurrent,
                'replaced_legacy_documents' => count($legacyDocumentIds),
                'legacy_document_ids' => $legacyDocumentIds,
                'message' => sprintf(
                    'Dry run: would ingest %d chunks into %s, replace %d existing source vectors, and remove %d legacy document(s).',
                    count($chunks),
                    $documentId,
                    $replacedCurrent,
                    count($legacyDocumentIds)
                ),
            ];
        }

        if ($sourceKey) {
            $replacedCurrent = $this->pineconeService->deleteByPrefix("{$documentId}_chunk-");
        }

        foreach ($legacyDocumentIds as $legacyDocumentId) {
            $replacedLegacy += $this->pineconeService->deleteByPrefix("{$legacyDocumentId}_chunk-");
        }

        $metadata = array_filter([
            'source_key' => $sourceKey,
            'source_label' => $sourceLabel,
            'source_url' => $sourceUrl,
        ], fn ($value) => filled($value));

        $this->pineconeService->storeChunks($chunks, $documentId, $metadata);

        $replacementSummary = [];

        if ($replacedCurrent > 0) {
            $replacementSummary[] = "replaced {$replacedCurrent} existing source vector(s)";
        }

        if ($replacedLegacy > 0) {
            $replacementSummary[] = "removed {$replacedLegacy} legacy vector(s)";
        }

        return [
            'ok' => true,
            'document_id' => $documentId,
            'chunk_count' => count($chunks),
            'replaced_current_vectors' => $replacedCurrent,
            'replaced_legacy_vectors' => $replacedLegacy,
            'legacy_document_ids' => $legacyDocumentIds,
            'message' => $replacementSummary === []
                ? $successMessage
                : $successMessage.' ('.implode('; ', $replacementSummary).')',
        ];
    }

    protected function extractPdfTextFromContents(string $contents): string
    {
        $document = (new Parser())->parseContent($contents);

        return $document->getText();
    }

    protected function findLegacyDocumentIds(array $needles): array
    {
        if ($needles === []) {
            return [];
        }

        $docIds = [];
        $chunkZeroIds = array_values(array_filter(
            $this->pineconeService->listAllVectorIds('doc_'),
            fn (string $id) => str_ends_with($id, '_chunk-0')
        ));

        foreach ($chunkZeroIds as $chunkZeroId) {
            $vector = $this->pineconeService->fetchVector($chunkZeroId);
            $text = Str::lower((string) ($vector['metadata']['text'] ?? ''));

            foreach ($needles as $needle) {
                if (Str::contains($text, Str::lower($needle))) {
                    $documentId = $vector['metadata']['document_id'] ?? null;

                    if (filled($documentId)) {
                        $docIds[] = $documentId;
                    }

                    break;
                }
            }
        }

        return array_values(array_unique($docIds));
    }

    protected function normalizeSourceKey(?string $sourceKey): ?string
    {
        $normalized = Str::slug((string) $sourceKey, '_');

        return $normalized !== '' ? $normalized : null;
    }

    protected function documentIdForSourceKey(string $sourceKey): string
    {
        return 'source_'.$sourceKey;
    }
}
