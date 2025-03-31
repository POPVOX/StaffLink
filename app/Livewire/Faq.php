<?php

namespace App\Livewire;

use Livewire\Component;

class Faq extends Component
{
    public function render()
    {
        $faqs = [
            [
                'question' => 'What is the StaffUp Portal?',
                'answer' => 'The StaffUp Portal is designed to help congressional staff with resources, FAQs, and operational tools.'
            ],
        ];

        return view('livewire.faq', compact('faqs'));
    }
}
