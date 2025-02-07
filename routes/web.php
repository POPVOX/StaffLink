<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Chatbot::class)->name('chatbot');
Route::get('/faq', \App\Livewire\Faq::class)->name('faq');

Route::get('/upload-doc', \App\Livewire\ProcessGoogleDoc::class);
