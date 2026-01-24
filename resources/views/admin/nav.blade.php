<form method="POST" action="{{ route('logout') }}">
    @csrf
    <a class="to_site" href="/" target="_blank" style="color:white; text-decoration:none; font-size:18px; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> До сайту
    </a>
    <a class="logout" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" style="color:white; text-decoration:none; font-size:18px; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-right-from-bracket"></i> Вихід
    </a>
</form>


<nav class="admin_menu active">
    <div class="in_admin_menu">

        <div class="admin_menu_link">
            <a href="{{ route('admin.books') }}" class="menu_link_item users_list">Книги</a>
        </div>

        <div class="admin_menu_link">
            <a href="{{ route('admin.characteristics') }}" class="menu_link_item users_list">Характеристики</a>
        </div>

        <div class="admin_menu_link">
            <a href="{{ route('admin.characteristicValues') }}" class="menu_link_item users_list">Значення характеристик</a>
        </div>

        <div class="admin_menu_link">
            <a href="{{ route('admin.tags') }}" class="menu_link_item users_list">Теги</a>
        </div>

        <div class="admin_menu_link">
            <a href="{{ route('admin.partners') }}" class="menu_link_item users_list">Партнери</a>
        </div>

        <div class="admin_menu_link">
            <a href="{{ route('admin.translates') }}" class="menu_link_item users_list">Переклади</a>
        </div>

        <div class="admin_menu_section">
            <div class="admin_menu_title">
                <div class="menu_title_item settings_menu_item menu_parent">Системні налаштування</div>
            </div>
            <div class="admin_menu_links">
                
    
    
                @if( Auth::user()->role === 0 )
                <div class="admin_menu_link">
                    <a href="{{ route('admin.users') }}" class="menu_link_item users_list">Користувачі</a>
                </div>
                @endif
    
    
    
                <div class="admin_menu_link">
                    <a href="{{ route('admin.settings') }}" class="menu_link_item menu_title_item settings_menu_item">Налаштування</a>
                </div>
    
                <div class="admin_menu_link">
                    <a href="{{ route('admin.seo') }}" class="menu_link_item users_list">Seo</a>
                </div>
    
    
    
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="admin_menu_link">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="menu_link_item">Вихід</a>
                    </div>
                </form>
                
            </div>
        </div>

    </div>








</nav>