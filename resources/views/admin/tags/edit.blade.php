@extends('layouts.admin')


@section('content')

<section class="add_user edit_user main_section active">
    <div class="title_h1">
        <div class="top_block">
            <a href="{{ route('admin.tags') }}" class="back_to">Назад</a>
        </div>
        <h1>Редагування</h1> 
    </div>
    
    <div class="form_block_items form_add form_edit">

        <?php
            $languages = ['ua', 'en'];
        ?>

        <form action="{{ route('admin.postTags') }}" method="POST"  autocomplete="off">
            <input type="text" autocomplete="username" name="fake_username" style="display:none;">
            <input type="password" autocomplete="new-password" name="fake_pass" style="display:none;">
            {{ csrf_field() }}
            <div class="select_bg"></div>

            <div class="form_block active fb_submit fb_submit_top">
                @include('admin.buttons')
            </div>


            <input type="hidden" name="id" value="{{$item->id ?? 0}}">
            <input type="hidden" name="languages" value={{ json_encode($languages) }} >

            <div class="langsForm">
                <div class="langsForm__headerCont">
                    @for ($i =0 ; $i < count($languages); $i++)
                        <div class="langsForm__lang {{ $i === 0 ? 'current' : '' }}">
                            {{ $languages[$i] }}
                        </div>
                    @endfor
                </div>
                <div class="langsForm__body">
                    @for ($i = 0; $i < count($languages); $i++)
                        <div class="langsForm__langFields {{ $i === 0 ? 'show' : '' }}">
                            <div class="form_block">
                                <div class="fb_inside">
                                    <div class="fb_label">
                                        <div class="fb_label_inside">
                                            <label for="name_{{ $languages[$i] }}">Назва {{ $languages[$i] }}</label>
                                        </div>
                                    </div>
                                    <div class="fb_input">
                                        <div class="fb_input_inside">
                                            <input
                                                type="text" 
                                                name="name[{{ $languages[$i] }}]" 
                                                value="{{ $item->translates[$languages[$i]]['name'] ?? '' }}" 
                                                id="name_{{ $languages[$i] }}" 
                                                required
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(isset($item))
                            <div class="form_block">
                                <div class="fb_inside">
                                    <div class="fb_label">
                                        <div class="fb_label_inside">
                                            <label for="slug_{{ $languages[$i] }}">Slug {{ $languages[$i] }}</label>
                                        </div>
                                    </div>
                                    <div class="fb_input">
                                        <div class="fb_input_inside">
                                            <input
                                                type="text" 
                                                value="{{ $item->translates[$languages[$i]]['slug'] ?? '' }}" 
                                                id="slug_{{ $languages[$i] }}" 
                                                disabled
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endfor

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
                            <input type="hidden" name="active" id="active" value="{{ $item->active ?? 1 }}">
                            <div class="toggle {{ isset($item) ? 
                                                    $item->active === 1 ? 'active' : '' 
                                                    : 'active'
                                                }}">
                                <span></span>
                            </div>
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