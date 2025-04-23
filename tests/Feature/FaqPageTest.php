<?php

use App\Livewire\Pages\Faq;
use App\Models\FaqCluster;
use Livewire\Livewire;

beforeEach(function () {
    FaqCluster::truncate();
});

it('renders the FAQ header and intro copy', function () {
    Livewire::test(Faq::class)
        ->assertSee('Frequently asked questions')
        ->assertSee('Here are a list of some questions that other users have asked');
});

it('shows no items when there are no clusters', function () {
    $html = Livewire::test(Faq::class)->html();
    expect(substr_count($html, '<dt'))->toBe(0);
});

it('lists clusters in descending frequency order with counts', function () {
    FaqCluster::create([
        'representative_text' => 'Low priority question',
        'frequency'           => 1,
    ]);
    FaqCluster::create([
        'representative_text' => 'High priority question',
        'frequency'           => 10,
    ]);

    Livewire::test(Faq::class)
        ->assertSeeInOrder([
            'High priority question',
            'Low priority question',
        ])
        ->assertSeeHtml('<span>10 have asked this</span>')
        ->assertSeeHtml('<span>1 have asked this</span>');
});
