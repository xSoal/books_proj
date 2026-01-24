@extends('layouts.front')

@section('content')
<div class="container">
<div class="popup_body">
        <div class="popup_left">

                <div class="popaup_header">
                    <div class="popup_headline">
                        <h4>Реєстрація</h4>
                    </div>

                </div>

                <form action="{{ route('register') }}" class="popup_form registartion active" method="POST">
                    @csrf
                    <div class="popup_fields registartion">

                        <div class="popup_field_item">
                            <label for="name">Прізвище та ім'я</label>
                            <input type="text" name="name" id="name" class="@error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>користувача не знайдено</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="popup_field_item">
                            <label for="email">E-mail</label>
                            <input type="text" name="email" id="email" class="@error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>Не вірно введена пошта</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="popup_field_item">
                            <label for="email">Телефон</label>
                            <input type="tel" name="phone" id="phone" placeholder='+380' class="@error('phone') is-invalid @enderror" value="{{ old('phone') }}" required autocomplete="phone" autofocus>
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>Перевірте номер телефону</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="popup_field_item password">
                            <label for="password">Пароль</label>
                            <input type="password" name="password" id="password" class="@error('password') is-invalid @enderror" required autocomplete="current-password">
                            <span class="pwd_show" id="pwd-show"></span>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>Не вірний пароль</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="popup_field_item password">
                            <label for="confirm-password">Підтвердити пароль</label>
                            <input type="password" name="password_confirmation" id="confirm-password" lass="@error('password_confirmation') is-invalid @enderror" required autocomplete="password_confirmation">
                            <span class="pwd_show" id="pwd-show"></span>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>Паролі не співпадають</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="popup_button">
                        <span class="popup_button_text">Реєстрація</span>
                        <img src="/images/icons/arrow-small-next.svg" alt="Вхід">
                    </button>
                </form>

                <div class="popup_bottom">
                    <a href="{{ route('login') }}" class="bottom_btns registration">
                        <span>Вхід</span>
                    </a>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="bottom_btns">
                        <span>Забули пароль?</span>
                    </a>
                    @endif
                </div>
                
            </div>
        </div>
    </div>

</div>
@endsection
