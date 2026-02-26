@extends('layouts.main_page')

@section('content')




<main id="main-content" >
    <section class="hero-section tinycont about_us">
        <div class="container ">
            <div class=" wysiwyg">
                {!! $about_us !!}
            </div>
        </div>
    </section>
</main>




@endsection