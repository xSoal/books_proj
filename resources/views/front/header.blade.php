
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
                    <li><a href="{{ route('browse') }}">{{ $translates['browse'] }}</a></li>
                    <li><a href="{{ route('search') }}">{{ $translates['search'] }}</a></li>
                    <li><a href="{{ route('about') }}">{{ $translates['aboute'] }}</a></li>
                </ul>
                <div class="language-selector" aria-label="Вибір мови">
                    <a href="{{ route('setlocale', ['lang' => 'ua'])  }}" lang="ua" aria-current="true" class="lang-link {{ app()->getLocale() === 'ua' ? 'active' : '' }}">UA</a>
                    <span aria-hidden="true">|</span>
                    <a href="{{ route('setlocale', ['lang' => 'en'])  }}" lang="en" class="lang-link {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                </div>
            </nav>
        </div>
    </header>