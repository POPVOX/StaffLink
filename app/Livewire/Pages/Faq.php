<?php

namespace App\Livewire\Pages;

use App\Models\FaqCluster;
use Livewire\Component;

class Faq extends Component
{
    public $faqs;

    public function mount()
    {
        $this->faqs = FaqCluster::where('frequency', '>=', 4)
            ->orderByDesc('frequency')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.faq');
    }
}
