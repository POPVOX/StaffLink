<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\DocumentProcessingService;
use Illuminate\Support\Facades\Storage;

class ProcessGoogleDoc extends Component
{
    use WithFileUploads;

    public string $url = '';
    public $document;
    public string $message = '';

    public function processDocument()
    {
        if ($this->document) {
            // Handle Text File Upload
            $path = $this->document->store('documents'); // Store file in storage/app/documents
            $text = Storage::get($path); // Read file content

            $result = app(DocumentProcessingService::class)->processPlainText($text);
        } elseif ($this->url) {
            // Handle Google Docs
            $result = app(DocumentProcessingService::class)->processGoogleDoc($this->url);
        } else {
            $this->message = "❌ Please provide a Google Docs link or upload a text file.";
            return;
        }

        $this->message = $result;
        $this->url = '';
        $this->document = null;
    }

    public function render()
    {
        return view('livewire.process-google-doc');
    }
}
