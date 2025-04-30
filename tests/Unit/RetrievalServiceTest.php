<?php

use App\Models\Correction;
use App\Services\RetrievalService;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('finds a high-priority correction by embedding similarity', function () {
    // 1) Seed a correction with a known example_embedding
    $correction = Correction::create([
        'question_pattern'  => 'internship length',
        'keywords'          => 'internship,length',
        'answer_text'       => 'House internships may last up to 4 months; Senate may allow 5 months.',
        'priority'          => 10,
        'active'            => true,
        // a simple 3-dimensional vector
        'example_embedding' => [1.0, 0.0, 0.0],
    ]);

    // 2) Stub OpenAIService to return the same vector for our test query
    $openAi = Mockery::mock(OpenAIService::class);
    $openAi
        ->shouldReceive('getEmbeddingVector')
        ->with('How long can an internship last?')
        ->andReturn([1.0, 0.0, 0.0]);
    app()->instance(OpenAIService::class, $openAi);

    // 3) Instantiate the service and call getCorrectionForQuery(...)
    $svc       = app(RetrievalService::class);
    $override  = $svc->getCorrectionForQuery('How long can an internship last?');

    // 4) Assert it returns the correction’s answer_text
    expect($override->answer_text)->toBe('House internships may last up to 4 months; Senate may allow 5 months.');
});
