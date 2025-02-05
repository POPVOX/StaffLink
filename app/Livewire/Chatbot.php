<?php

namespace App\Livewire;

use App\Services\OpenAIService;
use App\Services\RetrievalService;
use Livewire\Component;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class Chatbot extends Component
{
    public Conversation $conversation;
    public string $message = '';
    public bool $botTyping = false;
    protected RetrievalService $retrievalService;

    public function mount()
    {
        // Check for existing session ID or create a new conversation
        $sessionId = Session::get('chat_session_id');

        if ($sessionId) {
            $this->conversation = Conversation::where('session_id', $sessionId)->firstOrFail();
        } else {
            $this->conversation = Conversation::create(['session_id' => Str::random(12)]);
            Session::put('chat_session_id', $this->conversation->session_id);

            Message::create([
                'conversation_id' => $this->conversation->id,
                'role' => 'assistant',
                'content' => "<p>👋 Hi there! I'm your congressional office assistant. I can help answer questions about legislative processes, security, and compliance. Just ask!</p>",
            ]);
        }
    }

    public function sendMessage()
    {
        if (empty($this->message)) return;

        // Save user message
        Message::create([
            'conversation_id' => $this->conversation->id,
            'content' => $this->message,
            'role' => 'user',
        ]);

        // Refresh messages
        $this->conversation->refresh();

        // Show typing indicator
        $this->botTyping = true;
        $this->dispatch('scroll-to-bottom');

        // Capture user input before clearing
        $userMessage = $this->message;
        $this->message = '';

        $this->dispatch('generateBotResponse', $userMessage)->self();
    }

    #[\Livewire\Attributes\On('generateBotResponse')]
    public function generateBotResponse(string $userMessage)
    {
        // Simulate delay for UX
        sleep(1);

        $retrievedText = app(RetrievalService::class)->retrieveContextForQuery($userMessage);

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $userMessage],
        ];

        if (!empty($retrievedText)) {
            $messages[] = ['role' => 'system', 'content' => "Reference Material:\n" . $retrievedText];
        }

        try {
            $openAIService = app(OpenAIService::class);
            $botResponse = $openAIService->getChatResponse($messages);
        } catch (\Exception $e) {
            $botResponse = "I'm having trouble responding right now.";
        }

        Message::create([
            'conversation_id' => $this->conversation->id,
            'content' => $botResponse,
            'role' => 'assistant',
        ]);

        $this->conversation->refresh();

        $this->botTyping = false;
        $this->dispatch('scroll-to-bottom');
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

Always prioritize the documents stored in Pinecone for your answers. If the answer is unclear from these documents, say: "I don't have enough information to answer that based on available resources."

Reference comprehensive resources from the Modernization Staff Association, such as best practices guides, FAQs, onboarding checklists, and operational tools, to ensure your advice is actionable and aligned with established practices.
PROMPT;
    }

    private function getConversationHistory(): array
    {
        return $this->conversation->messages()->oldest()->get()->map(fn($msg) => [
            'role' => $msg->role === 'assistant' ? 'assistant' : 'user',
            'content' => $msg->content,
        ])->toArray();
    }
}
