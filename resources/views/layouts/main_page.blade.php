<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon32.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon32.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon180.ico') }}">

    <link rel="canonical" href="{{ Request::url() }}" />
    
    <link rel="stylesheet" href="{{asset('js/jquery-ui-1.13.1/jquery-ui.css')}}">
    
    <script src="{{ asset('/js/all.min.js') }}"></script>


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('/style/css/style.css') }}">
    <meta name="robots" content="index, follow" />

    <meta name="twitter:card" content="summary_large_image" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png" /> 
    <meta property="og:type" content="website" />

    @if($global_seo)

    <meta name="title" content="{{ $global_seo['meta_title'][app()->getLocale()] ?? '' }}" />
    <title>{{ $global_seo['meta_title'][app()->getLocale()] ?? '' }}</title>
    <meta name="description" content="{{ $global_seo['meta_description'][app()->getLocale()] ?? '' }}" />
    <meta name="keywords" content="{{ $global_seo['meta_keywords'][app()->getLocale()] ?? '' }}" />
    <meta property="og:title" content="{{ $global_seo['meta_title'][app()->getLocale()] ?? '' }}" />
    <meta property="og:image" content="{{ isset($global_seo['img']) && $global_seo['img'] !='' ? asset($global_seo['img']) : '' }}" />
    <meta property="og:description" content="{{ $global_seo['meta_description'][app()->getLocale()] ?? '' }}" />

    @elseif(isset($local_seo))
    <meta name="title" content="{{ $local_seo['meta_title'] ?? '' }}" />
    <title>{{ $local_seo['meta_title'] ?? '' }}</title>
    <meta name="description" content="{{ $local_seo['meta_description'] ?? '' }}" />
    <meta name="keywords" content="{{ $local_seo['meta_keywords'] ?? '' }}" />
    <meta property="og:title" content="{{ $local_seo['meta_title'] ?? '' }}" />
    <meta property="og:image" content="{{ isset($local_seo['img']) && $local_seo['img'] !='' ? asset($local_seo['img']) : '' }}" />
    <meta property="og:description" content="{{ $local_seo['meta_description'] ?? '' }}" />

    @else
        <title>Jewish Studies UA</title>
    @endif

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