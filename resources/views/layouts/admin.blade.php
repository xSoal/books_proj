<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" /> 
    <title>{{$title}}</title>

    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/tinymce/js/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('js/jquery-ui-1.13.1/jquery-ui.js')}}"></script>
    <script src="{{asset('js/jquery.multisortable.js')}}"></script>
    <script src="{{asset('js/jquery-ui-timepicker-addon.js')}}"></script>
    
    <link rel="stylesheet" href="{{asset('js/jquery-ui-1.13.1/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{asset('style/css/admin.css')}}?{{ time() }}">
    <link rel="icon" type="image/png" href="{{asset('images/favicon/favicon.png')}}" />
    <script src="{{ asset('/js/all.min.js') }}"></script>
    <script src="{{asset('js/admin.js')}}?{{ time() }}"></script>

</head> 
<body class="body_admin">
    <div class="preload_item"></div>
	<header>
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
       
        <div class="header_inner">
            <div class="header_elements">
                <div class="logo_burger">
                    <div class="burger_menu">
                        <div class="menu-btn open">
                            <div class="menu-btn__burger">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
	</header>
	
	@include('admin.nav')
	
	@yield('content')



</body>
</html>