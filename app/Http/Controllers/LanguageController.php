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
            $translatedFacts = [];
            $tr = new GoogleTranslate();
            $tr->setSource('en');
            $tr->setTarget(app()->getLocale());
            foreach($originalFacts as $fact){
                $translatedFacts[] = app()->getLocale() === 'en' ? $fact : $tr->translate($fact);
            }
            Session::put('translatedFacts', $translatedFacts);
        }
        return redirect('/')->with('locale', $lang);
    }

}
