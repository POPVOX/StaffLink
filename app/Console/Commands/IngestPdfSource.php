<?php

namespace App\Console\Commands;

use App\Services\DocumentProcessingService;
use Illuminate\Console\Command;

class IngestPdfSource extends Command
{
    protected $signature = 'rag:ingest-pdf
        {url : The PDF URL to ingest}
        {source_key : Stable source key used for replacement}
        {--label= : Human-readable source label}
        {--legacy-match=* : Text snippet used to find and remove legacy anonymous documents}
        {--dry-run : Report what would change without modifying Pinecone}';

    protected $description = 'Ingest a PDF into Pinecone with a stable source key and optional legacy replacement';

    public function handle(DocumentProcessingService $documents): int
    {
        $result = $documents->processPdfUrl($this->argument('url'), [
            'source_key' => $this->argument('source_key'),
            'source_label' => $this->option('label'),
            'legacy_matches' => $this->option('legacy-match'),
            'dry_run' => (bool) $this->option('dry-run'),
        ]);

        if (! ($result['ok'] ?? false)) {
            $this->error((string) ($result['message'] ?? 'Unable to ingest PDF.'));

            return self::FAILURE;
        }

        $this->info((string) $result['message']);
        $this->line('Document ID: '.($result['document_id'] ?? 'n/a'));
        $this->line('Chunk count: '.($result['chunk_count'] ?? 0));

        if (! empty($result['legacy_document_ids'] ?? [])) {
            $this->line('Legacy document IDs: '.implode(', ', $result['legacy_document_ids']));
        }

        return self::SUCCESS;
    }
}
