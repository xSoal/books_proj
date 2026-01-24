{{-- 
<header class="header">
	<div class="container">
		<div class="headerInner">
			<div class="headerLogo">
				<a href="/">
					<img src="/images/logo.svg" alt="Logo">
				</a>
			</div>
			<div class="headerMenu">
				<nav class="nav" role="navigation" aria-label="Головна навігація сайту">
					<ul class="headerMenuLinks">
						<li><a href="/news">News</a></li>
						<li><a href="/contacts">Contacts</a></li>
					</ul>
				</nav>
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
		</div>
	</div>
</header>
<div class="paddingHeader"></div> --}}


<a href="#main-content" class="skip-link">Перейти до основного змісту</a>

    <header class="site-header">
        <div class="container header-flex">
            <div class="logo">
                <a href="/" aria-label="Головна сторінка проєкту">
                    Jewish Studies <span class="accent-text">UA</span>
                </a>
            </div>

            <nav class="main-nav" aria-label="Головне меню">
                <ul class="nav-list">
                    <li><a href="#browse">Browse (Перегляд)</a></li>
                    <li><a href="#search">Search (Пошук)</a></li>
                    <li><a href="#about">About (Про проєкт)</a></li>
                </ul>
                <div class="language-selector" aria-label="Вибір мови">
                    <a href="{{ route('setlocale', ['lang' => 'ua'])  }}" lang="ua" aria-current="true" class="lang-link {{ app()->getLocale() === 'ua' ? 'active' : '' }}">UA</a>
                    <span aria-hidden="true">|</span>
                    <a href="{{ route('setlocale', ['lang' => 'en'])  }}" lang="en" class="lang-link {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                </div>
            </nav>
        </div>
    </header>