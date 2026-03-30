<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stichoza\GoogleTranslate\GoogleTranslate;

const URL = 'https://meowfacts.herokuapp.com/';
class FactsController extends Controller
{
    public function getFacts(Request $request){
        $locale = session()->get('locale', 'en');
        app()->setLocale($locale);

        $request->validate(['sliderValue' => 'required|integer|between:1,30']);
  
        $sliderValue = (int) $request->sliderValue;

        if (session()->has('facts_en') && session('facts_count') == $sliderValue) {
            $originalFacts = session('facts_en');
            $translatedFacts = $this->translate($originalFacts);
            return view('index', compact('translatedFacts', 'sliderValue'));
        }

        $originalFacts = [];
          
        for($i = 0; $i < $sliderValue; $i++){
            try{
                $fact = Http::get(URL);
                if (!$fact->successful()) {
                    return redirect()->back()->with('error', 'Unable to fetch facts at the moment. Please try again later.');
                }
            }catch(\Exception $e){
                return redirect()->back()->with('error', 'Network error. Please check your connection and try again.');
            }

            if(isset($fact['data'][0])){
                $originalFacts[] = $fact['data'][0];
            }
            else{
                return redirect()->back()->with('error', 'Unexpected response from the server. Please try again later.');
            }
        }
        session(['facts_en' => $originalFacts, 'facts_count' => $sliderValue]);
        $translatedFacts = $this->translate($originalFacts);
        return view('index', compact('translatedFacts', 'sliderValue'));
    }

    protected function translate($facts){
        if(app()->getLocale() == 'en'){ return $facts; }
        $tr = new GoogleTranslate();
        $tr->setSource('en');
        $tr->setTarget(app()->getLocale());

        $joinedFacts = implode(' ||| ', $facts);
        $result = $tr->translate($joinedFacts);
        return explode(' ||| ', $result);
    }
}
