@extends('layouts.main_page')

@section('content')

<main id="main-content">
    <section class="hero-section" aria-labelledby="contacts-title">
        <div class="container">
            <h1 id="contacts-title" class="section-title">Feedback</h1>
            <p class="hero-desc">
                We provide expertise in sectoral industrial development and business climate issues
            </p>
        </div>
    </section>

    <section class="feedback-section" aria-labelledby="contacts-form-title">
        <div class="container">
            <div class="contact-grid">
                <aside class="contact-card" aria-label="Contact details">
                    <div class="contact-block">
                        <h3 class="contact-block__title">Address</h3>
                        <p class="contact-block__text">1, Kotsyubynskoho Str., Kyiv</p>
                    </div>

                    <div class="contact-block">
                        <h3 class="contact-block__title">Reception</h3>
                        <p class="contact-block__text">+38 (044) 251-70-10</p>
                    </div>

                    <div class="contact-block">
                        <h3 class="contact-block__title">Social media</h3>
                        <div class="contact-socials">
                            <a href="https://www.facebook.com/UkrainiaEmployers/" target="_blank" rel="noopener" aria-label="Facebook">
                                <img src="/images/icons/facebook.svg" alt="" loading="lazy">
                            </a>
                            <a href="https://www.youtube.com/@UkrainianEmployers" target="_blank" rel="noopener" aria-label="YouTube">
                                <img src="/images/icons/youtube.svg" alt="" loading="lazy">
                            </a>
                            <a href="https://www.linkedin.com/company/federation-of-employers-of-ukraine/posts/?feedView=all" target="_blank" rel="noopener" aria-label="LinkedIn">
                                <img src="/images/icons/linkedin.svg" alt="" loading="lazy">
                            </a>
                        </div>
                    </div>
                </aside>

                <div class="form-wrapper form-wrapper--wide">
                    @if(isset($message))
                        <div id="slideInBox" class="contact-notice">
                            <p class="contact-notice__text">{{ $message }}</p>
                            <button type="button" class="btn btn-outline contact-notice__btn" onclick="hideBox()">
                                Сховати
                            </button>
                        </div>
                    @endif

                    <h2 id="contacts-form-title" class="section-title section-title--left">If you have any questions</h2>

                    <form class="contact-form" method="post" action="{{ route('main_page.contacts_submit') }}">
                        @csrf

                        <div class="field-group">
                            <label for="contact-name">Your Name</label>
                            <input id="contact-name" name="name" type="text" placeholder="Your Name" autocomplete="name" required>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label for="contact-email">E-mail</label>
                                <input id="contact-email" name="email" type="email" placeholder="E-mail" autocomplete="email" required>
                            </div>
                            <div class="field-group">
                                <label for="contact-phone">Mobile</label>
                                <input id="contact-phone" name="phone" type="text" placeholder="Mobile" autocomplete="tel">
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="contact-message">Message</label>
                            <textarea id="contact-message" name="message" rows="6" placeholder="Message" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-submit">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>


@endsection