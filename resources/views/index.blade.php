<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Meow Facts</title>
    
    <style>main{
        margin: 20px 40px 20px 40px;
    }

    .language-selector{
        position: fixed;
        top: 10px;
        right: 20px;
        z-index: 1000;
    }

    .generate-facts{
        position: relative;
        background-color: #8644bf;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        z-index: 100;
    }
    
    .share-modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 2000;
        min-width: 250px;
    }

    .share-modal-content ul {
        list-style: none;
        padding: 0;
        margin: 10px 0;
    }

    .share-modal-content li {
        padding: 8px 12px;
        margin: 5px 0;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .share-modal-content li:hover{
        background-color: #f0f0f0;
    }

    .overlay{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1500;
    }

    </style>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
<main>
    <div class="language-selector">
        <select onchange="location.href = this.value;">
            <option value="/setLanguage/en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
            <option value="/setLanguage/ru" {{ app()->getLocale() == 'ru' ? 'selected' : '' }}>Русский</option>
            <option value="/setLanguage/ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
        </select>
    </div>
    <br>

    <br>
    <h1><strong>{{ __('main.header') }}</strong></h1>
    <p>{{ __('main.welcome') }}</p>
    <br>

    @if (session('error'))
        <div style="color: red; margin-bottom: 10px;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color: red; margin-bottom: 10px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>    
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <form action="{{ route('generateFacts') }}" method="post" onsubmit="return showLoader(event)">
            @csrf
            <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
            <label for="slider">{{ __('main.select') }}</label>
            <input type="range" id="sliderInput" name="sliderValue" min=1 max=30
                value="{{ old('sliderValue', $sliderValue ?? 1) }}"
                oninput="document.getElementById('sliderOutput').value = value">
            <output id="sliderOutput">{{ old('sliderValue', $sliderValue ?? 1) }}</output>
            <br>
            <button type="submit" class="generate-facts">{{ __('main.generate_button') }}</button>
            <br>
        </form>
    </div>
    <br>

    @if (!empty($translatedFacts) || Session::has('translatedFacts'))
        <h3><strong>{{ __('main.facts') }}</strong></h3>
        <div class="scrollableList" style="max-height: 600px; overflow-y: auto; border: 1px solid #ccc;">
            <ol style="margin: 0; padding-left: 2rem; padding-right: 2rem; list-style-type: decimal; list-style-position: outside;">
                @php
                    $facts = $translatedFacts ?? Session::get('translatedFacts');
                @endphp
                @foreach ($facts as $fact)
                    <li>{{ $fact }} 
                        <button class="share-btn" onclick="shareFact('{{ addslashes($fact) }}')">📤</button>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    <div id="overlay" class="overlay" onclick="cancelShare()"></div>

    <div id="third-party-applications" class="share-modal">
        <div class="share-modal-content">
            <h3><strong>{{ __('main.share') }}</strong></h3>
            <ul>
                <li onclick="shareTo('facebook')">{{ __('main.facebook') }}</li>
                <li onclick="shareTo('whatsapp')">{{ __('main.whatsapp') }}</li>
                <li onclick="shareTo('telegram')">{{ __('main.telegram') }}</li>
                <li onclick="shareTo('email')">{{ __('main.email') }}</li>
            </ul>
            <button class="close-btn" onclick="cancelShare()"><strong>{{ __('main.cancel') }}</strong></button>
        </div>
    </div>

    <script>
        let currentFact = '';
        function showLoader(event) {
            var loader = document.getElementById('loader');
            loader.style.display = 'flex';
            return true;
        }

        function shareFact(fact){
            currentFact = fact;
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('third-party-applications').style.display = 'block';
        }

        function cancelShare(){
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('third-party-applications').style.display = 'none';
        }

        function shareTo(platform){
            const encodedFact = encodeURIComponent(currentFact);
            const encodedUrl = encodeURIComponent(window.location.href);
            let url = '';
            switch(platform){
                case 'facebook':
                    url = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedFact}`;
                    break;
                case 'whatsapp':
                    //url = `https://api.whatsapp.com/send?text=${encodedFact}%20${encodedUrl}`;
                    url = `https://wa.me/?text=${encodedFact}`;
                    break;
                case 'telegram':
                    url = `https://t.me/share/url?url=${encodedUrl}&text=${encodedFact}`;
                    break;
                case 'email':
                    url = `mailto:?subject=Check%20out%20this%20cat%20fact&body=${encodedFact}%20${encodedUrl}`;
                    break;    
            }
            if (url){
                window.open(url, '_blank', 'noopener,noreferrer');
            }
            cancelShare();
        }
    </script>

    <div id="loader" style="
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.9);
        justify-content: center;
        align-items: center;
        font-size: 30px;
        z-index: 9999;
        ">
        🐱 Loading...
    </div>
</main>

</body>
</html>