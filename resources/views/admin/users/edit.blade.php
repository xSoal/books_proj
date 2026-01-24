@extends('layouts.admin')


@section('content')

<section class="add_user edit_user main_section active">
    <div class="title_h1">
        <div class="top_block">
            <a href="{{ route('admin.users') }}" class="back_to">Назад</a>
        </div>
        <h1>Редагування</h1> 
    </div>
    
    <div class="form_block_items form_add form_edit">
        <form action="{{ route('admin.postUsers') }}" method="POST"  autocomplete="off">
            <input type="text" autocomplete="username" name="fake_username" style="display:none;">
            <input type="password" autocomplete="new-password" name="fake_pass" style="display:none;">
            
            {{ csrf_field() }}
            <div class="select_bg"></div>

            <div class="form_block active fb_submit fb_submit_top">
                @include('admin.buttons')
            </div>

            <input type="hidden" name="id" value="{{$item->id ?? 0}}">
            <div class="form_block active">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="fio">ПІБ</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input type="text" name="name" value="{{ $item->name ?? '' }}" id="fio" required>
                        </div>
                    </div>
                </div>
            </div>


            
            <div class="form_block active">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="product">Активний</label>
                        </div>
                    </div>
                    <div class="fb_input input_toggle">
                        <div class="fb_input_inside">
                            <input type="hidden" name="active" id="active" value="{{ $item->active ?? 0 }}">
                            <div class="toggle {{ isset($item) && $item->active == 1 ? 'active' : '' }}">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="email">Email</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input type="text" name="email" value="{{ $item->email ?? '' }}" id="email" required autocomplete="nope">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="password">Пароль</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input type="password" name="password" value="" id="password" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="repeat-pass">Повторіть пароль</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input type="password" name="password_confirmation" value="" id="repeat-pass" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block active fb_submit">
                @include('admin.buttons')
            </div>
        </form>
    </div>
</section>

@endsection