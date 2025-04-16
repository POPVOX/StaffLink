<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Chatbot::class)->name('chatbot');
Route::get('/faq', \App\Livewire\Faq::class)->name('faq');
Route::get('/resources', \App\Livewire\Pages\Resources::class)->name('resources');
Route::get('/about', \App\Livewire\Pages\About::class)->name('about');
Route::get('/privacy', \App\Livewire\Pages\Privacy::class)->name('privacy');

Route::get('/upload-doc', \App\Livewire\ProcessGoogleDoc::class);
