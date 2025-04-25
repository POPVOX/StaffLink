<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\QuestionEmbedding;
use App\Notifications\FeedbackSubmitted;
use App\Services\OpenAIService;
use App\Services\RetrievalService;
use Flux\Flux;
use Illuminate\Notifications\Slack\SlackRoute;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class Chatbot extends Component
{
    public Conversation $conversation;
    public string $message = '';
    public bool $botTyping = false;
    public string $feedbackDetails = '';
    public $queryString = [
        'message' => ['except' => ''],
    ];

    protected array $rules = [
        'feedbackDetails' => 'required|string|max:2000',
    ];

    public function mount()
    {
        $sessionId = Session::get('chat_session_id');

        if ($sessionId) {
            $this->conversation = Conversation::where('session_id', $sessionId)->firstOrFail();
        } else {
            $this->conversation = Conversation::create([
                'session_id' => Str::random(12),
            ]);
            Session::put('chat_session_id', $this->conversation->session_id);

            $welcome = Message::create([
                'conversation_id' => $this->conversation->id,
                'role'            => 'assistant',
                'content'         => "<p>👋 Hi there! I am a ChatBot trained on publicly available information from official sources and publications of the Modernization Staff Association. I can help answer questions about issues faced by junior Congressional staffers.</p>",
            ]);
        }

        if ($initial = request()->query('message')) {
            $this->message = $initial;
            $this->sendMessage();
        }

        $this->dispatch('scrollToBottom');
    }

    public function sendMessage()
    {
        if (trim($this->message) === '') {
            return;
        }

        $userMsg = Message::create([
            'conversation_id' => $this->conversation->id,
            'content'         => $this->message,
            'role'            => 'user',
        ]);

        $this->saveEmbedding($userMsg);

        $this->conversation->refresh();
        $this->botTyping = true;
        $this->dispatch('scrollToBottom');

        $userMessage = $this->message;
        $this->message = '';
        $this->dispatch('generateBotResponse', $userMessage)->self();
    }

    #[\Livewire\Attributes\On('generateBotResponse')]
    public function generateBotResponse(string $userMessage)
    {
        $retrievedText = app(RetrievalService::class)
            ->retrieveContextForQuery($userMessage);

        $messages = array_merge(
            [
                ['role' => 'system', 'content' => $this->systemPrompt()],
            ],
            $this->getConversationHistory(),          // <-- your helper
            [
                ['role' => 'user', 'content' => $userMessage],
            ]
        );

        if (! empty($retrievedText)) {
            $messages[] = [
                'role'    => 'system',
                'content' => "Reference Material:\n{$retrievedText}",
            ];
        }

        try {
            $botResponse = app(OpenAIService::class)
                ->getChatResponse($messages);
        } catch (\Exception $e) {
            $botResponse = "I'm having trouble responding right now.";
        }

        $botMsg = Message::create([
            'conversation_id' => $this->conversation->id,
            'content'         => $botResponse,
            'role'            => 'assistant',
        ]);

        $this->saveEmbedding($botMsg);

        $this->conversation->refresh();
        $this->botTyping = false;
        $this->dispatch('scrollToBottom');
    }

    /**
     * Generate & store an embedding for the given message.
     */
    protected function saveEmbedding(Message $msg): void
    {
        // get only the float[] from OpenAI
        $vector = app(OpenAIService::class)
            ->getEmbeddingVector($msg->content);

        if (! empty($vector)) {
            QuestionEmbedding::updateOrCreate(
                ['message_id' => $msg->id],
                ['embedding'  => $vector]
            );
        }
    }


    public function submitFeedback()
    {
        $this->validate();

        $sessionId = $this->conversation->session_id;
        $details   = $this->feedbackDetails;

        Flux::toast(
            heading: 'Thank you',
            text:    'Your feedback has been submitted.',
            variant: 'success'
        );

        Notification::route('slack', SlackRoute::make(
            config('services.slack.notifications.channel'),
            config('services.slack.notifications.bot_user_oauth_token')
        ))->notify(new FeedbackSubmitted($sessionId, $details));

        $this->feedbackDetails = '';
        $this->modal('feedback-modal')->close();
    }

    public function render()
    {
        return view('livewire.chatbot', [
            'messages' => $this->conversation->messages()->oldest()->get(),
        ]);
    }

    public function systemPrompt(): string
    {
        return <<<PROMPT
Please provide responses using **HTML formatting** for improved readability. Follow these rules:
- Use `<strong>` for bold headings.
- Use `<ul><li>` for bulleted lists and `<ol><li>` for numbered lists.
- Use `<p>` to separate different sections.
- Avoid large blocks of text; break content into **multiple paragraphs**.

Source and Information Integrity: Always prioritize the documents stored in Pinecone for your answers. If the answer is unclear from these documents, say: "I don't have enough information to answer that based on available resources." Under no circumstance should information be fabricated or embellished.

Audience and Tone: Tailor all responses for junior congressional staff, specifically legislative correspondents and staff assistants who are new to professional environments on the Hill. Provide a clear, step-by-step explanation using simple, actionable language. Include practical guidance on workplace etiquette and signal when it is advisable for them to seek further mentorship or additional resources from office leadership.

Legislative Process, Security, and Compliance: When responding to questions on the legislative process, provide detailed, accurate explanations based on the available resources. For security-related topics—given their high-stakes nature—ensure that the information is precise and fully supported by documentation. Similarly, for compliance matters, clearly define what is meant by "compliance" in the congressional context. Avoid generic or overly broad responses by making sure your answers are customized to the unique environment and responsibilities on the Hill. If the documents do not cover the requested detail, state clearly that the resource does not include specific guidance on that query.

Resource Reference: Reference comprehensive resources from the Modernization Staff Association, including best practices guides, FAQs, onboarding checklists, and operational tools, to ensure that all advice is actionable and aligned with established practices.
PROMPT;
    }

    private function getConversationHistory(): array
    {
        return $this->conversation->messages()->oldest()->get()
            ->map(fn($m) => [
                'role'    => $m->role === 'assistant' ? 'assistant' : 'user',
                'content' => $m->content,
            ])->toArray();
    }
}
