@extends('layouts.admin')


@section('content')

<section class="add_user edit_user main_section active bookPage">
    <div class="title_h1">
        <div class="top_block">
            <a href="{{ route('admin.books') }}" class="back_to">Назад</a>
        </div>
        <h1>Редагування</h1> 
    </div>
    
    <div class="form_block_items form_add form_edit">

        
        <script id="characteristics" type="application/json">{!! $characteristics !!}</script>
        <script id="chars_vals" type="application/json">{!! $chars_vals !!}</script>
        <script id="tags" type="application/json">{!! $tags !!}</script>
        

        @isset($item)
            <script id="book_chars_vals" type="application/json">{!! $book_chars_vals !!}</script>
            <script id="current_tags" type="application/json">{!! $current_tags !!}</script>
        @endisset


        <?php
            $languages = ['ua', 'en'];
            $characteristics_array = json_decode($characteristics, true);
            $char_vals_array = json_decode($chars_vals, true);

            if(isset($book_chars_vals)){
                $book_chars_vals = json_decode($book_chars_vals, true);
            }
        ?>

        
        <form action="{{ route('admin.postBooks') }}" method="POST"  autocomplete="off">
            <input type="text" autocomplete="username" name="fake_username" style="display:none;">
            <input type="password" autocomplete="new-password" name="fake_pass" style="display:none;">
            {{ csrf_field() }}
            <div class="select_bg"></div>

            <div class="form_block active fb_submit fb_submit_top">
                @include('admin.buttons')
            </div>


            <input type="hidden" name="id" value="{{$item->id ?? 0}}">
            <input type="hidden" name="languages" value={{ json_encode($languages) }} >
            
            <h2>Характеристики </h2>
            <div class="bookCharsSection">
                @isset($item)
                <h3>Характеристики книги </h3>
                <div class="book__charsCont">
                    @foreach ($book_chars_vals as $book_chars_val)
                    <?php
                        // id родительской характеристики для того, что было выбрано
                        $current_parent_ch_id = array_values(array_filter($char_vals_array, function($c) use ($book_chars_val) {
                            return $c['id'] === $book_chars_val['char_val_id'];
                        }))[0]['characteristic_id'];

                        // только те значение характеристик, которые принадлежат текущей родительской характеристике
                        $char_vals_from_current_parent = array_values(array_filter($char_vals_array, function($c) use ($current_parent_ch_id) {
                            return $c['characteristic_id'] === $current_parent_ch_id;
                        }));
                        // dd($char_vals_from_current_parent);

                    ?>
                        <div class="book__char">
                            <div class="book__charMain">
                                <select class="book__charMain__select">
                                    @foreach ($characteristics_array as $char)
                                        <option
                                            value="{{ $char['id'] }}"
                                            {{ $current_parent_ch_id === $char['id'] ? 'selected' : '' }}
                                        >{{ $char['translates']['ua']['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="book__charVal">
                                <select name="book_chars_vals[]" class="book__charVal__select">
                                    @foreach ($char_vals_from_current_parent as $char_val)
                                        <option
                                            value="{{ $char_val['id'] }}"
                                            {{ $book_chars_val['char_val_id'] === $char_val['id'] ? 'selected' : '' }}
                                        >{{ $char_val['translates']['ua']['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="bookCharDel"><i class="fa-regular fa-trash-can"></i></div>
                        </div>
                    @endforeach
                    {{-- <div class="book__char">
                        <div class="book__charMain">
                            <select class="book__charMain__select">
                                @foreach ($characteristics_array as $char)
                                    <option>{{ $char['translates']['ua']['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="book__charVal">
                            <select class="book__charVal__select">
                                @foreach ($characteristics_array as $char)
                                    <option>{{ $char['translates']['ua']['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}
                </div>
                @endisset
                <button type="button" class="btn-add-char book__addCharCont" id="addCharacteristic">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                    </svg>
                    Додати характеристику
                </button>
            </div>
            <br>
            <div class="tagsCont">
                <h2>Поточні вибрані теги: </h2>
                <div class="currentTags">
                    @foreach ($current_tags as $tag)
                        <div class="tag" data-id="{{ $tag['id'] }}">
                            {{ $tag['translates']['ua']['name'] }}
                            <input hidden name="tags[]" value="{{ $tag['id'] }}}">
                            <span class="tag-remove" data-id="{{ $tag['id'] }}">×</span>
                        </div>
                    @endforeach
                </div>
                <div class="addTagsCont">
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_input_inside">
                                <input id="add_tag" type="text" value="" placeholder="Почніть вводити тег...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="addTags__searchResultCont">
                    </div>
                </div>
            </div>
            <br><br><br>
            <div class="langsForm">
                <div class="langsForm__headerCont">
                    @for ($i =0 ; $i < count($languages); $i++)
                        <div class="langsForm__lang {{ $i === 0 ? 'current' : '' }}">
                            {{ $languages[$i] }}
                        </div>
                    @endfor
                </div>
                <div class="langsForm__body">
                    <?php

                    ?>
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
                            <div class="form_block">    
                                <div class="fb_inside">
                                    <div class="fb_label">
                                        <div class="fb_label_inside">
                                            <label for="anotation_{{ $languages[$i] }}">Анотації {{ $languages[$i] }}</label>
                                        </div>
                                    </div>
                                    <div class="fb_input">
                                        <div class="fb_input_inside">
                                            <textarea 
                                                name="anotation[{{ $languages[$i] }}]"
                                                id="anotation_{{ $languages[$i] }}"
                                                cols="30"
                                                rows="10">{{ $item->translates[$languages[$i]]['anotation'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>                                
                            </div>

                        </div>
                    @endfor

                </div>
            </div>

            <div class="form_block active">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="sotr">Порядок в сортуванні</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input
                                type="number"
                                min='0' 
                                name="sort" 
                                value="{{ isset($item) ? $item->sort : 0 }}" 
                                id="sort" 
                            >
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

            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="image">Зображення</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <button type="button" class="addPhotoBtn"></button>
                            <input type="file" class="addPhoto" data-name="img" data-type="book">
                            <div class="photoPreview">
                                @if( isset($item) && $item->img !='' )
                                <div class="preview">
                                    <img src="{{ $item->img }}">
                                    <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                    <input type="hidden" name="img" value="{{ $item->img }}">
                                </div>
                                @endif
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