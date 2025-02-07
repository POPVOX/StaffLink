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
            [
                'question' => 'How do you make holy water?',
                'answer' => 'You boil the hell out of it. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas cupiditate laboriosam fugiat.'
            ],
            [
                'question' => 'What\'s the best thing about Switzerland?',
                'answer' => 'I don\'t know, but the flag is a big plus. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas cupiditate laboriosam fugiat.'
            ],
            [
                'question' => 'What do you call someone with no body and no nose?',
                'answer' => 'Nobody knows. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas cupiditate laboriosam fugiat.'
            ],
            [
                'question' => 'Why do you never see elephants hiding in trees?',
                'answer' => 'Because they\'re so good at it. Lorem ipsum dolor sit amet consectetur adipisicing elit. Quas cupiditate laboriosam fugiat.'
            ],
        ];

        return view('livewire.faq', compact('faqs'));
    }
}
