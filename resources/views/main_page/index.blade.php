@extends('layouts.main_page')

@section('content')



<main id="main-content">
  <section class="hero-section">
      <div class="container">
          <div class="about_us wysiwyg">
            {!! $about_us !!}
          </div>  
          <div class="cta-group">
              <a href="{{ route('search') }}" class="btn btn-primary">{{ $translates['start_search'] }}</a>
              <a href="{{ route('browse') }}" class="btn btn-outline">{{ $translates['see_all_recordings'] }}</a>
          </div>
      </div>
  </section>

    <section class="partners-section" aria-labelledby="partners-title">
        <div class="container">
            <h2 id="partners-title" class="section-title">{{ $translates['partners'] }}</h2>
            
            <div class="partners-grid">
                @foreach ($partners as $partner)
                    <div class="partner-card">
                        <div class="partner-logo-box">
                            <img src="{{ $partner->img }}" alt="{{ $partner->translates[app()->getLocale()]['name'] }} logo">
                        </div>
                        
                        <div class="partner-details">
                            <h3 class="partner-name">{{ $partner->translates[app()->getLocale()]['name'] }}</h3>
                            @if($partner->link)
                                <a href="{{ $partner->link }}" class="partner-link-static" target="_blank" rel="noopener">
                                    {{ $translates['site_link'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

  <section class="feedback-section" aria-labelledby="feedback-title">
      <div class="container">
          <div class="form-wrapper">
              <h2 id="feedback-title" class="section-title">{{ $translates['feedback'] }}</h2>
              <p class="form-intro" id="feedback-help">{{ $translates['feedback_help_text'] }}</p>
              
              <form action="#" method="post" class="contact-form" aria-describedby="feedback-help">
                  @csrf
                  <div class="field-group">
                      <label for="user-name">{{ $translates['contact_name'] }}</label>
                      <input type="text" id="user-name" name="name" required autocomplete="name">
                  </div>
                  <div class="field-group">
                      <label for="user-email">{{ $translates['contact_email'] }}</label>
                      <input type="email" id="user-email" name="email" required autocomplete="email">
                  </div>
                  <div class="field-group">
                      <label for="user-msg">{{ $translates['contact_message'] }}</label>
                      <textarea id="user-msg" name="message" rows="5" required></textarea>
                  </div>
                  <button type="submit" class="btn btn-submit">{{ $translates['contact_send_message'] }}</button>
              </form>
          </div>
      </div>
  </section>
</main>




@endsection