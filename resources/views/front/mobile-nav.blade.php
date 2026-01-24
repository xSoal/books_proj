{{-- <header class="mobHeader heder">
    <div class="site-container mobHeader__wrapper">
        <a href="/" class="siteLogo">
            <div class="headerLogo">
					<img src="/images/logo.svg" alt="Logo">
			</div>
        </a>

        <button class="navToggleBtn" aria-expanded="false" aria-controls="mobileNavOverlay">
            <span class="btnLine"></span>
            <span class="btnLine"></span>
            <span class="btnLine"></span>
        </button>
        
        <nav class="mobileNavOverlay" id="mobileNavOverlay">
            <ul class="menuList">
                <li><a href="/news">News</a></li>
                <li><a href="/contacts">Contacts</a></li>
            </ul>

            <div class="">
				@if( auth()->user() )
					<ul class="headerMenuLinks loginHref">
						<li class="headerSubmenuCont">
							<img src="/images/icons/userAuth.svg">
							<div class="headerSubmenu">
								<a href="/admin" target="_blank">Admin panel</a>
								<a href="#" class="exitBtn">Log out</a>
								<form action="{{ route('logout') }}" method="POST">@csrf</form>
							</div>

						</li>
					</ul>
				@else
					<a class="loginHref" href="{{ route('login') }}" >
						<img src="/images/icons/user.svg">
					</a>
				@endif
            </div>
        </nav>
    </div>
</header> --}}