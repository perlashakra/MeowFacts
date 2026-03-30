<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LanguageController extends Controller
{
    public function setLanguage($lang){
        if(in_array($lang, ['en', 'ru', 'ar'])){
            App::setLocale($lang);
            Session::put('locale', $lang);
        }

        if(session()->has('facts_en')){
            $originalFacts = session()->get('facts_en');
            if(app()->getLocale() === 'en'){
                $translatedFacts = $originalFacts;
            } else {
                $tr = new GoogleTranslate();
                $tr->setSource('en');
                $tr->setTarget(app()->getLocale());
                $joinedFacts = implode(' ||| ', $originalFacts);
                $translatedFacts = explode('||| ', $tr->translate($joinedFacts));
            }
            Session::put('translatedFacts', $translatedFacts);
        }
        return redirect('/')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

}
