<?php

use App\Models\Correction;
use App\Models\Keyword;
use App\Services\RetrievalService;
use App\Services\OpenAIService;
use App\Services\PineconeService;

afterEach(function () {
    Mockery::close();
});

it('finds a high-priority correction by embedding similarity', function () {
    $correction = Correction::create([
        'question_pattern'  => 'internship length',
        'answer_text'       => 'House internships may last up to 4 months; Senate may allow 5 months.',
        'priority'          => 10,
        'active'            => true,
        'example_embedding' => [1.0, 0.0, 0.0],
    ]);

    $kw1 = Keyword::create(['name' => 'internship']);
    $kw2 = Keyword::create(['name' => 'length']);
    $correction->keywords()->attach([$kw1->id, $kw2->id]);

    $openAi = Mockery::mock(OpenAIService::class);
    $openAi
        ->shouldReceive('getEmbeddingVector')
        ->once()
        ->with('How long can an internship last?')
        ->andReturn([1.0, 0.0, 0.0]);
    app()->instance(OpenAIService::class, $openAi);

    $pinecone = Mockery::mock(PineconeService::class);
    // retrieveRelevantChunks() isn't used by getCorrectionForQuery(), but we bind it anyway
    $pinecone->shouldReceive('retrieveRelevantChunks')->andReturn([]);
    app()->instance(PineconeService::class, $pinecone);

    $svc      = app(RetrievalService::class);
    $override = $svc->getCorrectionForQuery('How long can an internship last?');

    expect($override)->toBeInstanceOf(Correction::class)
        ->and($override->answer_text)
        ->toBe('House internships may last up to 4 months; Senate may allow 5 months.');
});
