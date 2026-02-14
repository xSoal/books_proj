@extends('layouts.admin')


@section('content')

<section class="product_type main_section active">
    <div class="title_h1">
        <h1>Налаштуванная</h1>
    </div>

    
    <br><br><br>

    {{-- Проверка на наличие сообщения об успехе (success) --}}
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    {{-- Проверка на наличие сообщения об ошибке (error) --}}
    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="form_block_items form_add form_edit">
        <form action="{{ route('admin.settings_updateEmail') }}" method="POST">
            {{ csrf_field() }} 
            <div class="select_bg"></div>

            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="email">Пошта для форми  зв'язку</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input id="email" name="email" value="{{ $email ?? '' }}" required>
                        </div>
                    </div>
                </div>
            </div>
            <h1>Про нас на головній сторінці</h1>

            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="ua">UA</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <textarea name="ua" id="ua" class="textarea_item">{{ $about_us->ua ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="en">EN</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <textarea id="en" name="en" class="textarea_item">{{ $about_us->en ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <h1>Про нас на окремій сторінці</h1>

            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="about_us_full_ua">UA</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <textarea name="about_us_full_ua" id="about_us_full_ua" class="textarea_item">{{ $about_us_full->ua ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="about_us_full_en">EN</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <textarea id="about_us_full_en" name="about_us_full_en" class="textarea_item">{{ $about_us_full->en ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form_block active fb_submit">
                <div class="fb_inside">
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <button class="btn-save" name="save" value="true" type="submit">Зберегти</button>
                        </div>
                    </div>
                </div>
            </div>
           
        </form>
    </div>

</section>

@endsection