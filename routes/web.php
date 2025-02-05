<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Chatbot::class)->name('chatbot');
Route::get('/upload-doc', \App\Livewire\ProcessGoogleDoc::class);
