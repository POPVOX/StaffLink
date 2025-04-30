<?php

use App\Notifications\FeedbackSubmitted;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use App\Livewire\Chatbot;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\OpenAIService;
use App\Services\RetrievalService;

beforeEach(function () {
    Notification::fake();

    $openAi = Mockery::mock(OpenAIService::class);
    $openAi
        ->shouldReceive('generateEmbedding')
        ->andReturn([ ['embedding' => [0.1, 0.2, 0.3] ] ]);
    $openAi
        ->shouldReceive('getEmbeddingVector')
        ->andReturn([0.1, 0.2, 0.3]);
    $openAi
        ->shouldReceive('getChatResponse')
        ->andReturn('🤖 Mock bot reply');
    app()->instance(OpenAIService::class, $openAi);

    $retrieval = Mockery::mock(RetrievalService::class);
    $retrieval
        ->shouldReceive('getCorrectionForQuery')
        ->andReturn(null);
    $retrieval
        ->shouldReceive('retrieveContextForQuery')
        ->andReturn('');
    app()->instance(RetrievalService::class, $retrieval);
});

afterEach(function () {
    Mockery::close();
});

it('initializes a conversation with a welcome message and fires scrollToBottom', function () {
    expect(Conversation::count())->toBe(0);
    expect(Message::count())->toBe(0);

    Livewire::test(Chatbot::class)
        ->assertSeeHtml('👋 Hi there!')
        ->assertDispatched('scrollToBottom');

    expect(Conversation::count())->toBe(1);
    expect(Message::count())->toBe(1);
});

it('saves a user message, clears the input, shows typing, and fires scrollToBottom', function () {
    Livewire::test(Chatbot::class)
        ->set('message', 'Hello testing')
        ->call('sendMessage')
        ->assertSet('message', '')
        ->assertSet('botTyping', true)
        ->assertDispatched('scrollToBottom');

    expect(
        Message::where('content', 'Hello testing')
            ->where('role', 'user')
            ->exists()
    )->toBeTrue();
});

it('generates a bot response, persists it, resets typing, and fires scrollToBottom', function () {
    // our global fake will return "🤖 Mock bot reply"
    $test = Livewire::test(Chatbot::class)
        ->set('message', 'Hello bot')
        ->call('sendMessage')
        ->assertSet('botTyping', true)
        ->assertDispatched('scrollToBottom');

    $test
        ->call('generateBotResponse', 'Hello bot')
        ->assertSet('botTyping', false)
        ->assertDispatched('scrollToBottom');

    $assistantMessages = Message::where('role', 'assistant')->pluck('content')->toArray();

    expect($assistantMessages)->toContain('🤖 Mock bot reply');
    expect(count($assistantMessages))->toBe(2);
});

it('stores embeddings for both user and bot messages', function () {
    Livewire::test(Chatbot::class)
        ->set('message', 'Test embedding')
        ->call('sendMessage')
        ->call('generateBotResponse', 'Test embedding')
        ->assertSeeHtml('🤖 Mock bot reply')
        ->assertSet('botTyping', false);

    expect(\App\Models\QuestionEmbedding::count())->toBe(2);
});

it('dispatches the correct toast when feedback is submitted', function () {
    Livewire::test(Chatbot::class)
        ->set('feedbackDetails', 'This is a test feedback')
        ->call('submitFeedback')
        ->assertDispatched('toast-show', function (string $eventName, array $params) {
            expect($params['slots']['text'])->toBe('Your feedback has been submitted.');
            expect($params['slots']['heading'])->toBe('Thank you');
            expect($params['dataset']['variant'])->toBe('success');
            expect($params['duration'])->toBe(5000);
            return true;
        });

    Notification::assertSentTimes(FeedbackSubmitted::class, 1);
});
