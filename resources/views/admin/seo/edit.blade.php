@extends('layouts.admin')


@section('content')

<section class="add_category edit_category main_section news_admin active">
    <div class="title_h1">
        
        <div class="top_block">
            <a href="/admin" class="back_to">Назад</a>
        </div>
        
        <h1>Seo</h1>

    </div>
    <div class="form_block_items form_add form_edit">

    @php
        $languages = ['ua', 'en'];
        $pages = ['main_page', 'search', 'about', 'browse'];
        $seo_tags = ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description'];
    @endphp
        <div class="tabPagesHeaderCont">
            @foreach ($pages as $i => $page)
                <div class="tabPagesHeader {{ $i === 0 ? 'active' : '' }}">
                    {{ $page }}
                </div>
            @endforeach   
        </div>
        <div class="tabPagesBodyCont">
            

                @foreach ($pages as $i => $page)
                    <form action="{{ route('admin.seoEdit') }}" method="POST">
        
                        {{ csrf_field() }}
                        <input type="hidden" name="page" value="{{ $page }}" >

                        <div class="tabBody__page {{ $i === 0 ? 'show' : '' }}" data-index="{{ $i }}">

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
                                                @foreach ($seo_tags as $tag)
                                                    <div class="form_block">
                                                        <div class="fb_inside">
                                                            <div class="fb_label">
                                                                <div class="fb_label_inside">
                                                                    <label for="{{ $languages[$i] }}_{{ $tag }}">{{ $tag }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="fb_input">
                                                                <div class="fb_input_inside">
                                                                    <?php
                                                                        // dd( $seo[$page][$tag][$languages[$i]] );
                                                                    ?>
                                                                    <input type="text" name="{{ $tag }}[{{ $languages[$i] }}]" value="{{ isset($seo[$page]) ? $seo[$page][$tag][$languages[$i]] : ''}}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <div class="form_block">
                                                    <div class="fb_inside">
                                                        <div class="fb_label">
                                                            <div class="fb_label_inside">
                                                                <label for="image">Лого компанії</label>
                                                            </div>
                                                        </div>
                                                        <div class="fb_input">
                                                            <div class="fb_input_inside">
                                                                <button type="button" class="addPhotoBtn"></button>
                                                                <input type="file" class="addPhoto" data-name="img" data-type="seo_images">
                                                                <div class="photoPreview">
                                                                    <input type="hidden" name="img_container_exists" value="1">

                                                                    @if( isset($seo[$page]) && isset($seo[$page]['img']) )
                                                                        <div class="preview">
                                                                            <img src="{{ $seo[$page]['img'] }}">
                                                                            {{-- При удалении просто убираем блок. Контроллер поймет, что инпута с таким именем больше нет --}}
                                                                            <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                                                            {{-- Уникальное имя инпута для страницы --}}
                                                                            <input type="hidden" name="{{ $page }}_img" value="{{ $seo[$page]['img'] }}">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                
                                <div class="form_block active fb_submit">
                                    <div class="fb_inside">
                                        <div class="fb_input">
                                            <div class="fb_input_inside">
                                                    <button class="btn-save" type="submit">Зберегти</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </form>

                @endforeach

        </div>


</section>
@endsection