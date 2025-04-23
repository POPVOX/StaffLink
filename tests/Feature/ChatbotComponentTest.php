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
    app()->instance(OpenAIService::class, new class {
        public function getChatResponse(array $messages): string
        {
            return 'Mocked reply';
        }
        public function getEmbeddingVector(string $text): array
        {
            return [0.1, 0.2, 0.3];
        }
    });

    app()->instance(RetrievalService::class, new class {
        public function retrieveContextForQuery(string $q): string
        {
            return '';
        }
    });

    $test = Livewire::test(Chatbot::class)
        ->set('message', 'Hello bot')
        ->call('sendMessage')
        ->assertSet('botTyping', true)
        ->assertSet('message', '')
        ->assertDispatched('scrollToBottom');

    $test->call('generateBotResponse', 'Hello bot')
        ->assertSet('botTyping', false)
        ->assertDispatched('scrollToBottom');

    $assistantMessages = Message::where('role', 'assistant')->pluck('content')->toArray();

    expect($assistantMessages)->toContain('Mocked reply');
    expect(count($assistantMessages))->toBe(2);
});

it('generates a bot response, persists it, and stops typing', function () {
    $retrieval = \Mockery::mock(\App\Services\RetrievalService::class);
    $retrieval
        ->shouldReceive('retrieveContextForQuery')
        ->once()
        ->with('Hey bot')
        ->andReturn('');
    app()->instance(\App\Services\RetrievalService::class, $retrieval);

    $openAi = \Mockery::mock(\App\Services\OpenAIService::class);
    $openAi
        ->shouldReceive('getChatResponse')
        ->once()
        ->with([
            ['role' => 'system', 'content' => app(\App\Livewire\Chatbot::class)->systemPrompt()],
            ['role' => 'user',   'content' => 'Hey bot'],
        ])
        ->andReturn('🤖 Mock reply');
    $openAi
        ->shouldReceive('getEmbeddingVector')
        ->twice()
        ->andReturn([0.1, 0.2, 0.3]);
    app()->instance(\App\Services\OpenAIService::class, $openAi);

    Livewire::test(\App\Livewire\Chatbot::class)
        ->set('message', 'Hey bot')
        ->call('sendMessage')                        // saves user msg + dispatches scroll
        ->call('generateBotResponse', 'Hey bot')     // triggers our stubbed reply
        ->assertSeeHtml('🤖 Mock reply')             // the bot’s reply is rendered
        ->assertSet('botTyping', false);             // typing indicator is turned off

    expect(\App\Models\Message::where('role', 'assistant')
        ->where('content', '🤖 Mock reply')
        ->exists()
    )->toBeTrue();
});

it('stores embeddings for both user and bot messages', function () {
    $retrieval = \Mockery::mock(\App\Services\RetrievalService::class);
    $retrieval
        ->shouldReceive('retrieveContextForQuery')
        ->once()
        ->andReturn('');
    app()->instance(\App\Services\RetrievalService::class, $retrieval);

    $openAi = \Mockery::mock(\App\Services\OpenAIService::class);
    $openAi
        ->shouldReceive('getChatResponse')
        ->andReturn('👍 Embedding test reply');
    $openAi
        ->shouldReceive('getEmbeddingVector')
        ->twice()
        ->andReturn([0.1, 0.2, 0.3]);
    app()->instance(\App\Services\OpenAIService::class, $openAi);

    Livewire::test(\App\Livewire\Chatbot::class)
        ->set('message', 'Test embedding')
        ->call('sendMessage')
        ->call('generateBotResponse', 'Test embedding')
        ->assertSeeHtml('👍 Embedding test reply')
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



