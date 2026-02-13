@extends('layouts.main_page')

@section('content')


<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Вхід до системи</h1>
        <p class="auth-subtitle">Тільки для адміністраторів та редакторів проєкту</p>
        
        <form action="{{ route('login') }}" class="auth-form" method="post">
            @csrf
            <div class="form-group">
                <label for="email">Електронна пошта</label>
                <input type="email" id="email" name="email" required placeholder="example@mail.com">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            {{-- <div class="auth-options">
                <label class="checkbox-container">
                    <input type="checkbox"> Запам'ятати мене
                </label>
                <a href="#" class="forgot-link">Забули пароль?</a>
            </div> --}}

            <button type="submit" class="btn-login">Увійти</button>
        </form>
    </div>
</main>


@endsection
