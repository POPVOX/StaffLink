<?php

use App\Services\DocumentProcessingService;

afterEach(function () {
    Mockery::close();
});

it('passes the pdf ingest options through to the document processing service', function () {
    $service = Mockery::mock(DocumentProcessingService::class);
    $service->shouldReceive('processPdfUrl')
        ->once()
        ->with('https://example.com/house-guide.pdf', Mockery::on(function (array $options) {
            return $options['source_key'] === 'house-salc-guide'
                && $options['source_label'] === 'House SA/LC Guide 119th'
                && $options['legacy_matches'] === ['118th Congress – HOUSE']
                && $options['dry_run'] === true;
        }))
        ->andReturn([
            'ok' => true,
            'message' => 'Dry run complete.',
            'document_id' => 'source_house_salc_guide',
            'chunk_count' => 42,
            'legacy_document_ids' => ['doc_old_house'],
        ]);

    app()->instance(DocumentProcessingService::class, $service);

    $this->artisan('rag:ingest-pdf', [
        'url' => 'https://example.com/house-guide.pdf',
        'source_key' => 'house-salc-guide',
        '--label' => 'House SA/LC Guide 119th',
        '--legacy-match' => ['118th Congress – HOUSE'],
        '--dry-run' => true,
    ])
        ->expectsOutput('Dry run complete.')
        ->expectsOutput('Document ID: source_house_salc_guide')
        ->expectsOutput('Chunk count: 42')
        ->expectsOutput('Legacy document IDs: doc_old_house')
        ->assertExitCode(0);
});
