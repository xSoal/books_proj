@extends('layouts.main_page')

@section('content')



{{ route('login') }}

{{ url(App\Http\Middleware\LocaleMiddleware::getLocale() .'/home') }}


<main id="main-content">
  <section class="hero-section" aria-labelledby="hero-title">
      <div class="container">
          <div class="about_us">
            {!! $about_us !!}
          </div>  
          <div class="cta-group">
              <a href="{{ route('search') }}" class="btn btn-primary">Почати пошук</a>
              <a href="#browse" class="btn btn-outline">Переглянути всі записи</a>
          </div>
      </div>
  </section>

  <section class="partners-section" aria-labelledby="partners-title">
      <div class="container">
          <h2 id="partners-title" class="section-title">Партнери проєкту</h2>
          <div class="flex-row partners-list">
            @foreach ($partners as $partner )
                <div class="partner-card">
                    <div class="partner-img">
                        <img src="{{ $partner->img }}" alt="Partner {{ $partner->name }} logo">
                    </div>
                    <div class="partner-name">{{ $partner->translates[app()->getLocale()]['name'] }}</div>
                    @if($partner->link)
                        <div class="partner-link">
                            <a href="{{ $partner->link }}">Посилання</a>
                        </div>
                    @endif
                </div>
            @endforeach
          </div>
      </div>
  </section>

  <section class="feedback-section" aria-labelledby="feedback-title">
      <div class="container">
          <div class="form-wrapper">
              <h2 id="feedback-title" class="section-title">Зворотний зв’язок</h2>
              <p class="form-intro">Ваш досвід допоможе нам покращити платформу.</p>
              
              <form action="#" method="post" class="contact-form">
                  <div class="field-group">
                      <label for="user-name">Ваше ім’я</label>
                      <input type="text" id="user-name" name="name" required autocomplete="name">
                  </div>
                  <div class="field-group">
                      <label for="user-email">Електронна пошта (Email)</label>
                      <input type="email" id="user-email" name="email" required autocomplete="email">
                  </div>
                  <div class="field-group">
                      <label for="user-msg">Повідомлення або відгук</label>
                      <textarea id="user-msg" name="message" rows="5" required></textarea>
                  </div>
                  <button type="submit" class="btn btn-submit">Надіслати повідомлення</button>
              </form>
          </div>
      </div>
  </section>
</main>




@endsection