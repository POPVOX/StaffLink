<?php

use App\Services\OpenAIService;
use App\Services\PineconeService;
use Illuminate\Support\Facades\Http;

it('stores chunk embeddings in pinecone with the configured host', function () {
    config()->set('services.pinecone.api_key', 'test-key');
    config()->set('services.pinecone.index_host', 'https://example-pinecone.test');

    $openAi = Mockery::mock(OpenAIService::class);
    $openAi->shouldReceive('getEmbeddingVector')->once()->with('Chunk A')->andReturn([1, 2.5, 3]);
    app()->instance(OpenAIService::class, $openAi);

    Http::fake([
        'https://example-pinecone.test/vectors/upsert' => Http::response(['upsertedCount' => 1], 200),
    ]);

    $response = app(PineconeService::class)->storeChunks(['Chunk A'], 'doc-123');

    expect($response->json('upsertedCount'))->toBe(1);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example-pinecone.test/vectors/upsert'
            && $request->hasHeader('Api-Key', 'test-key')
            && $request['vectors'][0]['id'] === 'doc-123_chunk-0'
            && $request['vectors'][0]['values'] === [1.0, 2.5, 3.0]
            && $request['vectors'][0]['metadata']['text'] === 'Chunk A';
    });
});

it('returns matching chunk text from pinecone query results', function () {
    config()->set('services.pinecone.api_key', 'test-key');
    config()->set('services.pinecone.index_host', 'https://example-pinecone.test');

    $openAi = Mockery::mock(OpenAIService::class);
    $openAi->shouldReceive('getEmbeddingVector')->once()->with('What is the policy?')->andReturn([0.1, 0.2, 0.3]);
    app()->instance(OpenAIService::class, $openAi);

    Http::fake([
        'https://example-pinecone.test/query' => Http::response([
            'matches' => [
                ['metadata' => ['text' => 'Chunk 1']],
                ['metadata' => ['text' => 'Chunk 2']],
            ],
        ], 200),
    ]);

    $results = app(PineconeService::class)->retrieveRelevantChunks('What is the policy?', 2);

    expect($results)->toBe(['Chunk 1', 'Chunk 2']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example-pinecone.test/query'
            && $request['vector'] === [0.1, 0.2, 0.3]
            && $request['topK'] === 2
            && $request['includeMetadata'] === true;
    });
});
