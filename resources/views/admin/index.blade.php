@extends('layouts.admin')

@section('content')
    <div class="main_section active main_page_admin">

        <header>
            <div style="display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto;">
                <div style="font-size:24px; font-weight:bold; display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-user-tie"></i> Адмін-панель
                </div>
            </div>
        </header>
        
        
        <div class="container">
        <!-- Перша група -->
        <div class="section-title">Основні розділи</div>
        <div class="grid">
            <a class="card-link" href="#"><div class="card"><div class="icon"><i class='fa-solid fa-newspaper'></i></div>Новини</div></a>
            <a class="card-link" href="#"><div class="card"><div class="icon"><i class='fa-solid fa-hand-holding-heart'></i></div>Фінансові інструменти підтримки</div></a>
            <a class="card-link" href="#"><div class="card"><div class="icon"><i class='fa-solid fa-scale-balanced'></i></div>Правила регулювання</div></a>
        </div>
        
        
        <!-- Друга група -->
        <div class="section-title">Партнерські розділи</div>
        <div class="grid">
            <a class="card-link" href="#"><div class="card"><div class="icon"><i class='fa-solid fa-building-columns'></i></div>Institutional Partners</div></a>
            <a class="card-link" href="#"><div class="card"><div class="icon"><i class='fa-solid fa-user-group'></i></div>Participants</div></a>
            <a class="card-link" href="#"><div class="card"><div class="icon"><i class='fa-solid fa-building-columns'></i></div>Dealers and Service</div></a>
        </div>
        
        
        <!-- Користувачі -->
        </div>
    </div>

@endsection