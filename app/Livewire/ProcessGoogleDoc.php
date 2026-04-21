<?php

namespace App\Livewire;

use App\Services\DocumentProcessingService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProcessGoogleDoc extends Component
{
    use WithFileUploads;

    public string $url = '';

    public string $sourceKey = '';

    public string $sourceLabel = '';

    public string $legacyMatches = '';

    public $document;

    public string $message = '';

    public function processDocument()
    {
        $options = [
            'source_key' => $this->sourceKey,
            'source_label' => $this->sourceLabel,
            'legacy_matches' => preg_split('/\r\n|\r|\n/', $this->legacyMatches) ?: [],
        ];

        if ($this->document) {
            $path = $this->document->store('documents');

            $result = app(DocumentProcessingService::class)->processUploadedFile(
                $path,
                $this->document->getClientOriginalName(),
                $options
            );
        } elseif ($this->url) {
            $service = app(DocumentProcessingService::class);

            $result = str_ends_with(strtolower($this->url), '.pdf')
                ? $service->processPdfUrl($this->url, $options)['message']
                : $service->processGoogleDoc($this->url, $options);
        } else {
            $this->message = '❌ Please provide a Google Docs link, PDF URL, or upload a text/PDF file.';

            return;
        }

        $this->message = $result;
        $this->url = '';
        $this->sourceKey = '';
        $this->sourceLabel = '';
        $this->legacyMatches = '';
        $this->document = null;
    }

    public function render()
    {
        return view('livewire.process-google-doc');
    }
}
