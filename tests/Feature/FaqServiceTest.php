<?php

use App\Models\Message;
use App\Models\QuestionEmbedding;
use App\Models\FaqCluster;
use App\Services\FaqService;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\DB;

it('clusters similar questions and persists clusters using LLM output', function () {
    // seed messages
    $msg1 = Message::create([
        'conversation_id' => 1, 'role' => 'user', 'content' => 'How do I request my pay stub?',
    ]);
    $msg2 = Message::create([
        'conversation_id' => 1, 'role' => 'user', 'content' => 'How can I access my pay stub or view my paycheck details?',
    ]);
    $msg3 = Message::create([
        'conversation_id' => 1, 'role' => 'user', 'content' => 'What is the cafeteria menu today?',
    ]);

    // embeddings: two close, one orthogonal
    $close = array_fill(0, 5, 0.1);
    QuestionEmbedding::create(['message_id' => $msg1->id, 'embedding' => $close]);
    QuestionEmbedding::create(['message_id' => $msg2->id, 'embedding' => $close]);
    // use an alternating vector so cos-sim < 0.5 at threshold=0.5
    $far = [1, -1, 1, -1, 1];
    QuestionEmbedding::create(['message_id' => $msg3->id, 'embedding' => $far]);


    // mock the OpenAIService
    $mock = Mockery::mock(OpenAIService::class);
    $mock->shouldReceive('getChatResponse')
        ->once()
        ->withArgs(function (array $messages) {
            // first message is the system prompt
            if ($messages[0]['role'] !== 'system'
                || $messages[0]['content'] !== 'You are a helpful assistant that writes FAQ questions.') {
                return false;
            }
            // second message should contain both bullets exactly
            $c = $messages[1]['content'];
            return str_contains($c, '- “How do I request my pay stub?”')
                && str_contains($c, '- “How can I access my pay stub or view my paycheck details?”');
        })
        ->andReturn('How do I get my pay stub?');

    app()->instance(OpenAIService::class, $mock);

    // run clustering with low threshold
    app(FaqService::class)->recluster(0.5);

    // assert one cluster
    expect(FaqCluster::count())->toBe(1);

    $faq = FaqCluster::first();
    expect($faq->representative_text)->toBe('How do I get my pay stub?')
        ->and($faq->frequency)->toBe(2);

    // pivot links to msg1 & msg2 only
    $linked = DB::table('faq_cluster_message')
        ->where('cluster_id', $faq->id)
        ->pluck('message_id')
        ->all();

    expect($linked)->toContain($msg1->id)
        ->toContain($msg2->id)
        ->not->toContain($msg3->id);
});
