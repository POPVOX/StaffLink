<?php

namespace App\Livewire\Admin;

use Flux\Flux;
use Livewire\Component;
use App\Models\Correction;
use App\Models\Keyword;
use Illuminate\Validation\Rule;

class Corrections extends Component
{
    public $corrections;

    public $showModal    = false;
    public $modalMode    = 'create'; // or 'edit'
    public $allKeywords      = [];
    public $selectedKeywords = [];
    public ?Correction $editing = null;

    public $question_pattern = '';
    public $answer_text      = '';
    public $priority         = 0;
    public $active           = true;
    public $showAddNew       = false;
    public string $newKeyword = '';

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('corrections', 'question_pattern');
        if ($this->editing) {
            $uniqueRule = $uniqueRule->ignore($this->editing->id);
        }

        return [
            'question_pattern'   => ['required','string',$uniqueRule],
            'selectedKeywords'   => ['required','array'],
            'selectedKeywords.*' => ['integer','exists:keywords,id'],
            'answer_text'        => ['required','string'],
            'priority'           => ['required','integer','min:0'],
            'active'             => ['boolean'],
            'newKeyword'         => ['nullable','string','max:50','unique:keywords,name'],
        ];
    }

    public function mount()
    {
        $this->loadCorrections();
        $this->loadKeywords();
    }

    public function loadCorrections(): void
    {
        $this->corrections = Correction::orderByDesc('priority')->get();
    }

    private function loadKeywords(): void
    {
        $this->allKeywords = Keyword::orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function edit(Correction $correction)
    {
        $this->editing          = $correction;
        $this->question_pattern = $correction->question_pattern;
        $this->selectedKeywords = $correction->keywords()->pluck('id')->toArray();
        $this->answer_text      = $correction->answer_text;
        $this->priority         = $correction->priority;
        $this->active           = $correction->active;
        $this->modalMode        = 'edit';
        $this->showModal        = true;
    }

    public function save()
    {
        $data = $this->validate();

        $keywordIds = $data['selectedKeywords'];
        unset($data['selectedKeywords']);

        if ($this->modalMode === 'create') {
            $correction = Correction::create($data);
        } else {
            $this->editing->update($data);
            $correction = $this->editing;
        }

        $correction->keywords()->sync($keywordIds);

        $this->showModal = false;

        Flux::toast(
            heading: 'Success!',
            text:    'Your correction has been saved.',
            variant: 'success'
        );

        $this->loadCorrections();
    }

    public function delete(Correction $correction)
    {
        $correction->delete();
        $this->loadCorrections();
    }

    /**
     * Create a new Keyword on the fly and attach it.
     */
    public function addKeyword()
    {
        // validate just the new keyword
        $this->validateOnly('newKeyword');

        // create it
        $kw = Keyword::create([
            'name' => $this->newKeyword,
        ]);

        // reload all options
        $this->allKeywords = Keyword::orderBy('name')->get();

        // select it immediately
        $this->selectedKeywords[] = $kw->id;

        // clear the input
        $this->newKeyword = '';
    }

    private function resetForm()
    {
        $this->editing          = null;
        $this->question_pattern = '';
        $this->selectedKeywords = [];
        $this->answer_text      = '';
        $this->priority         = 0;
        $this->active           = true;
    }

    public function resetModalState(): void
    {
        // Close the modal on server–side, in case someone calls directly
        $this->showModal = false;

        // Reset all your form‐fields back to defaults
        $this->reset([
            'question_pattern',
            'selectedKeywords',
            'newKeyword',
            'answer_text',
            'priority',
            'active',
            'showAddNew'
        ]);
    }

    public function render()
    {
        return view('livewire.admin.corrections');
    }
}
