@extends('layouts.front')

@section('content')
<div class="container">

    <div class="popup_body">
        <div class="popup_left">

                <div class="popaup_header">
                    <div class="popup_headline">
                        <h4>Відновлення пароля</h4>
                    </div>
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" class="popup_form forgot_password active" method="POST">
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
                    </div>

                    <button type="submit" class="popup_button sign_in_btn">
                        <span class="popup_button_text">Відправити</span>
                        <img src="/images/icons/arrow-small-next.svg" alt="Вхід">
                    </button>
                </form>

                <div class="popup_bottom">
                    <a href="{{ route('register') }}" class="bottom_btns">
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

</div>
@endsection
