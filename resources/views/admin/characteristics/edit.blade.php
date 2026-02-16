@extends('layouts.admin')


@section('content')

<section class="add_user edit_user main_section active">
    <div class="title_h1">
        <div class="top_block">
            <a href="{{ route('admin.characteristics') }}" class="back_to">Назад</a>
        </div>
        <h1>Редагування</h1> 
    </div>
    
    <div class="form_block_items form_add form_edit">

        <?php
            $languages = ['ua', 'en'];
        ?>

        <form action="{{ route('admin.postCharacteristics') }}" method="POST"  autocomplete="off">
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
                            <div class="form_block">    
                                <div class="fb_inside">
                                    <div class="fb_label">
                                        <div class="fb_label_inside">
                                            <label for="description_{{ $languages[$i] }}">Подробиці {{ $languages[$i] }}</label>
                                        </div>
                                    </div>
                                    <div class="fb_input">
                                        <div class="fb_input_inside">
                                            <textarea 
                                                name="description[{{ $languages[$i] }}]"
                                                id="description_{{ $languages[$i] }}"
                                                cols="30"
                                                rows="10">{{ $item->translates[$languages[$i]]['name'] ?? '' }}</textarea>
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

            <div class="form_block in_filter">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="product">В фільтрі</label>
                        </div>
                    </div>
                    <div class="fb_input input_toggle">
                        <div class="fb_input_inside">
                            <input type="hidden" name="in_filter" id="in_filter" value="{{ $item->in_filter ?? 1 }}">
                            <div class="toggle {{ isset($item) ? 
                                                    $item->in_filter === 1 ? 'active' : '' 
                                                    : 'active'
                                                }}">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block is_numeric">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="product">Числовий фільтр</label>
                        </div>
                    </div>
                    <div class="fb_input input_toggle">
                        <div class="fb_input_inside">
                            <input type="hidden" name="is_numeric" id="is_numeric" value="{{ $item->is_numeric ?? 1 }}">
                            <div class="toggle {{ isset($item) ? 
                                                    $item->is_numeric === 1 ? 'active' : '' 
                                                    : 'active'
                                                }}">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block can_sorted_by">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="product">Можно сортувати за</label>
                        </div>
                    </div>
                    <div class="fb_input input_toggle">
                        <div class="fb_input_inside">
                            <input type="hidden" name="can_sorted_by" id="can_sorted_by" value="{{ $item->can_sorted_by ?? 1 }}">
                            <div class="toggle {{ isset($item) ? 
                                                    $item->can_sorted_by === 1 ? 'active' : '' 
                                                    : 'active'
                                                }}">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block can_sorted_by">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="is_author">Динамічне поле "Автор"</label>
                        </div>
                    </div>
                    <div class="fb_input input_toggle">
                        <div class="fb_input_inside">
                            <input type="hidden" name="is_author" id="is_author" value="{{ $item->is_author ?? 1 }}">
                            <div class="toggle {{ isset($item) ? 
                                                    $item->is_author === 1 ? 'active' : '' 
                                                    : 'active'
                                                }}">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form_block can_sorted_by">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="is_type">Динамічне поле "Тип видання"</label>
                        </div>
                    </div>
                    <div class="fb_input input_toggle">
                        <div class="fb_input_inside">
                            <input type="hidden" name="is_type" id="is_type" value="{{ $item->is_type ?? 1 }}">
                            <div class="toggle {{ isset($item) ? 
                                                    $item->is_type === 1 ? 'active' : '' 
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
                            <input type="file" class="addPhoto" data-name="img" data-type="characteristic">
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