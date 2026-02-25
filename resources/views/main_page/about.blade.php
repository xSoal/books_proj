@extends('layouts.main_page')

@section('content')




<main id="main-content">
    <section class="hero-section tinycont">
        <div class="container">
            <h1 class="section-title section-title--left">{{ $translates['about'] ?? 'About' }}</h1>
            <div class="about_us wysiwyg">
                {!! $about_us !!}
            </div>
        </div>
    </section>
</main>




@endsection