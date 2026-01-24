@extends('layouts.admin')


@section('content')

<section class="add_user edit_user main_section active">
    <div class="title_h1">
        <div class="top_block">
            <a href="{{ route('admin.translates') }}" class="back_to">Назад</a>
        </div>
        <h1>Редагування</h1> 
    </div>
    
    <div class="form_block_items form_add form_edit">
        <form action="{{ route('admin.postTranslate') }}" method="POST"  autocomplete="off">
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
                            <label for="slug">Slug</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input type="text" name="slug" {{ isset($item) && $item->slug ? 'disabled' : '' }} value="{{ $item->slug ?? '' }}" id="slug" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block active">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="name_ua">Ua</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input name="ua" type="text" value="{{ $item->ua ?? '' }}" id="fio" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form_block active">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="name_en">En</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input name="en" type="text" value="{{ $item->en ?? '' }}" id="fio" required>
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