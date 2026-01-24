@extends('layouts.main_page')

@section('content')


<div class="mainImage__cont">
    <div class="mainImage__background">
      <div class="mainImage__black"></div>
      <div class="mainImage__image" style="background-image: url( /images/login.jpg )"></div>
      <div class="mainImage__filter"></div>
    </div>
    <div class="mainImage__content">
      <div class="container">
        <div class="container-inner">
          <h2 class="h2">Authorization</h2>
        </div>
      </div>
    </div>
</div>

<div class=" loginCont">
    <div class="login-page-container">
        <form action="{{ route('login') }}" class="login-form" method="post">
            @csrf
            <div class="form-group">
                <label for="loginField">Login *</label>
                <p class="">Please fill in this field</p>
                {{-- <p class="validation-error">Please fill in this field</p> --}}
                {{-- <input type="text" id="loginField" class="form-control error-input" required> --}}
                @error('email')
                    <p class="validation-error" role="alert">
                        <strong>Incorrectly entered data</strong>
                    </p>
                @enderror
                
                <input type="text" name="email" id="loginField" class="form-control" required>
            </div>
    
            <div class="form-group">
                <label for="passwordInput">Password *</label>
                @error('password')
                    <span class="ivalidation-error" role="alert">
                        <strong>Incorrectly entered data</strong>
                    </span>
                @enderror
                <div class="password-container">
                    <div class="password-icon-wrapper">
                        <i class="fas fa-key"></i> 
                    </div>
                    <input type="password" 
                           name="password"
                           id="passwordInput" 
                           class="form-control password-input" 
                           required>
                    <button type="button" 
                            id="togglePassword" 
                            class="toggle-password">
                            <div class="div-fa-eye">
                                <i class="fas fa-eye"></i> 
                            </div>
                            <div class="div-fa-eye-slash hidden">
                                <i class="fas fa-eye-slash" ></i> 
                            </div>
                    </button>
                </div>
            </div>
    
            <div class="form-group checkbox-group">
                <input type="checkbox" id="rememberMe">
                <label for="rememberMe">Remember me</label>
            </div>
    
            <button type="submit" class="btn btn-primary btn-submit">Sign in</button>
        </form>
    </div>
    
</div>


{{-- <div class="container">

    <div class="popup_body">
        <div class="popup_left">

                <div class="popaup_header">
                    <div class="popup_headline">
                        <h4>Авторизація</h4>
                    </div>
                    
                </div>

                <form action="{{ route('login') }}" class="popup_form sign_in active" method="POST">
                    @csrf
                    <div class="popup_fields sign_in">
                        <div class="popup_field_item">
                            <label for="email">E-mail</label>
                            <input type="text" name="email" id="email" class="@error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>Не вірно введена пошта</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="popup_field_item password">
                            <label for="password">Пароль</label>
                            <input type="password" name="password" id="password" class="@error('password') is-invalid @enderror" required autocomplete="current-password">
                            <span class="pwd_show" id="pwd-show"></span>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="popup_button sign_in_btn">
                        <span class="popup_button_text">Вхід</span>
                        <img src="/images/icons/arrow-small-next.svg" alt="Вхід">
                    </button>
                </form>

                <div class="popup_bottom">
                    <a href="{{ route('register') }}" class="bottom_btns registration">
                        <span>Реєстрація</span>
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
<div> --}}



@endsection
