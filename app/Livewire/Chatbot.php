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

        $this->dispatch('scrollToBottom');
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
        $this->dispatch('scrollToBottom');

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
        $this->dispatch('scrollToBottom');
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
        return $this->conversation->messages()->oldest()->get()->map(fn($msg) => [
            'role' => $msg->role === 'assistant' ? 'assistant' : 'user',
            'content' => $msg->content,
        ])->toArray();
    }
}
