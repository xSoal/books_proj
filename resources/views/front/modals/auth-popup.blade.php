<div class="popup_item sign_in">
    <div class="popup_body">
        <div class="popup_left">
            <div class="popaup_header">
                <div class="popup_headline">
                    <h4>Authorization</h4>
                </div>
            </div>
           

            <form action="{{ route('login') }}" class="popup_form sign_in active" method="POST">
                @csrf
                <div class="popup_fields sign_in">
                    <div class="popup_field_item">
                        <label for="email">E-mail</label>
                        <input type="text" name="email" id="email">
                    </div>

                    <div class="popup_field_item password">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password">
                        <span class="pwd_show" id="pwd-show"></span>
                    </div>
                </div>

                <button class="popup_button sign_in_btn">
                    <span class="popup_button_text">Login</span>
                    <img src="/images/icons/arrow-small-next.svg" alt="Вхід">
                </button>
            </form>

            <form action="{{ route('register') }}" class="popup_form registration" method="POST">
                @csrf
                <div class="popup_fields">
                    <div class="popup_field_item">
                        <label for="name">Last Name and First Name</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="popup_field_item">
                        <label for="email">E-mail</label>
                        <input type="text" name="email" id="email" required>
                    </div>
                    <div class="popup_field_item">
                        <label for="email">Phone</label>
                        <input type="tel" name="phone" id="phone" placeholder='+380' required>
                    </div>

                    <div class="popup_field_item password">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password">
                        <span class="pwd_show" id="pwd-show"></span>
                    </div>
                    <div class="popup_field_item password">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="confirm-password">
                        <span class="pwd_show" id="pwd-show"></span>
                    </div>
                </div>

                <button class="popup_button">
                    <span class="popup_button_text">Register</span>
                    <img src="/images/icons/arrow-small-next.svg" alt="Вхід">
                </button>
            </form>

            <form action="" class="popup_form forgot_password">
                <div class="popup_fields">
                    
                    <div class="popup_field_item">
                        <label for="email">Your e-mail</label>
                        <input type="text" name="email" id="email">
                    </div>
                    
                </div>

                <button class="popup_button">
                    <span class="popup_button_text">Send</span>
                    <img src="/images/icons/arrow-small-next.svg" alt="Вхід">
                </button>
            </form>

            <div class="popup_bottom">
                <div id="toggleButton" class="bottom_btns registration">
                    <span>Register</span>
                </div>

                <div id="forgotButton" class="bottom_btns">
                    <span>Forgot your password?</span>
                </div>
            </div>
            
        </div>
    </div>
</div>