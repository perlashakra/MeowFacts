<?php

use App\Http\Controllers\FactsController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index', ['translatedFacts' => session('translatedFacts'), 
    'sliderValue' => session('facts_count')]);
});

Route::get('/setLanguage/{lang}', [LanguageController::class, 'setLanguage'])->name('setLanguage');

Route::post('/facts', [FactsController::class, 'getFacts'])->name('generateFacts');
Route::get('/facts', function(){ return  redirect('/'); });



