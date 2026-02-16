@extends('layouts.main_page')

@section('content')


<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">{{ $translates['login_to_system'] }}</h1>
        <p class="auth-subtitle">{{ $translates['for_admins_only'] }}</p>
        
        <form action="{{ route('login') }}" class="auth-form" method="post">
            @csrf
            <div class="form-group">
                <label for="email">{{ $translates['contact_email'] }}</label>
                <input type="email" id="email" name="email" required placeholder="{{ $translates['contact_email'] }}">
            </div>

            <div class="form-group">
                <label for="password">{{ $translates['password'] }}</label>
                <input type="password" id="password" name="password" required placeholder="{{ $translates['password'] }}">
            </div>

            {{-- <div class="auth-options">
                <label class="checkbox-container">
                    <input type="checkbox"> Запам'ятати мене
                </label>
                <a href="#" class="forgot-link">Забули пароль?</a>
            </div> --}}

            <button type="submit" class="btn-login">{{ $translates['log_in'] }}</button>
        </form>
    </div>
</main>


@endsection
