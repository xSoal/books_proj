<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon32.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon32.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon180.ico') }}">

    {{-- <meta name="description" content="{{ $meta_decs ?? '' }}"> --}}
    
    <link rel="canonical" href="{{ Request::url() }}" />


    
    
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> --}}
    
    {{-- <script src="https://kit.fontawesome.com/5370645651.js" crossorigin="anonymous"></script> --}}
    <link rel="stylesheet" href="{{asset('js/jquery-ui-1.13.1/jquery-ui.css')}}">
    
    <script src="{{ asset('/js/all.min.js') }}"></script>

    {{-- <link rel="stylesheet" href="{{ asset('/style/css/old_styles.css') }}"> --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet"> --}}
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('/style/css/style.css') }}">
    {{-- @if( isset($LoginTitle) )
    <title>{{ $LoginTitle  ?? ''}}</title>
    @else
    <title>{{ $title  ?? ''}}</title>
    @endif

    <meta name="robots" content="index, follow" />
    <meta name="title" content="{{ $seo->meta_title ?? '' }}" />
    <meta name="description" content="{{ $seo->meta_description ?? '' }}" />
    <meta name="keywords" content="{{ $seo->meta_keywords ?? '' }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $seo->og_title ?? '' }}" />
    <meta property="og:description" content="{{ $seo->og_description ?? '' }}" />
    <meta property="og:image" content="{{ isset($seo) && $seo->og_img !='' ? asset($seo->og_img) : '' }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png" /> --}}

</head>
<body class="site helix-ultimate hu com_sppagebuilder com-sppagebuilder view-page layout-default task-none itemid-101 uk-ua ltr sticky-header layout-fluid offcanvas-init offcanvs-position-right">
    
    @if( count($errors) > 0 )
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if( session('status') )
        <div class="session_stat">
            {{ session('status') }}
        </div>
    @endif


    <div class="body-wrapper">
        <div class="body-innerwrapper">
            @include('front.header')
            @include('front.mobile-nav')

            @yield('content')

            {{-- @include('front.modals.auth-popup') --}}
            @include('front.footer')
            <div class="popup_bg"></div>
            <div class="search_bg"></div>
            <div class="search_bg_transparent"></div>
        </div>
    </div>
    <a href="#" class="sp-scroll-up scrollToTopBtn" aria-label="Scroll Up"><span class="fas fa-angle-up" aria-hidden="true"></span></a>

    <script src="{{ asset('/js/jquery.min.js') }}"></script>
    <script src="{{asset('js/tinymce/js/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('js/jquery-ui-1.13.1/jquery-ui.js')}}"></script>
    <script src="{{asset('js/jquery-ui-timepicker-addon.js')}}"></script>
    <script type="module" src="{{ asset('/js/script.js') }}"></script>
</body>
</html>