<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" type="image/svg+xml">

    <meta name="description" content="{{ $meta_decs ?? '' }}">
    
    <link rel="canonical" href="{{ Request::url() }}" />


    <link rel="apple-touch-icon" sizes="180x180" href='/images/apple-touch-icon.png'>
    
    
    <script src="https://kit.fontawesome.com/5370645651.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{asset('js/jquery-ui-1.13.1/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{ asset('/style/css/style.css') }}">


    <title>{{ $meta_title  ?? ''}}</title>
</head>
<body>
    
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


    <div class="wrapper">
        @include('front.header')
        @include('front.mobile-nav')
        <main>
        @yield('content')
        </main>
        @include('front.modals.auth-popup')
        @include('front.footer')
        <div class="popup_bg"></div>
        <div class="search_bg"></div>
        <div class="search_bg_transparent"></div>
    </div>


    <script src="{{ asset('/js/jquery.min.js') }}"></script>
    <script src="{{asset('js/tinymce/js/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('js/jquery-ui-1.13.1/jquery-ui.js')}}"></script>
    <script src="{{asset('js/jquery-ui-timepicker-addon.js')}}"></script>
    <script type="module" src="{{ asset('/js/script.js') }}"></script>
</body>
</html>