@extends('layouts.front')

@section('content')
<div class="container">

<div class="popup_body">
        <div class="popup_left">

                <div class="popaup_header">
                    <div class="popup_headline">
                        <h4>Відновлення паролю</h4>
                    </div>
                </div>

                <form action="{{ route('password.update') }}" class="popup_form forgot_password" method="POST">
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
                    <div  class="bottom_btns registration">
                        <span>Реєстрація</span>
                    </div>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="bottom_btns">
                        <span>Забули пароль?</span>
                    </a>
                    @endif
                </div>
                
            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
