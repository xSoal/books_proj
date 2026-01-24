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
        $pages = ['main_page', 'search', 'aboute_us'];
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
                                                                    <?php
                                                                        // dd($seo[$page]['img']);
                                                                    ?>
                                                                    @if( isset($seo[$page]) && isset($seo[$page]['img'])))
                                                                    <div class="preview">

                                                                        <img src="{{  $seo[$page]['img'] }}">
                                                                        <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                                                        <input type="hidden" name="img" value="{{  $seo[$page]['img'] }}">
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

        {{-- <form action="{{ route('admin.seoEdit') }}" method="POST">
            {{ csrf_field() }} 
            <input type="hidden" name="type" value="news">
            <div class="select_bg"></div>

            
            <div class="project_doube_row">

                <div class="project_left">

                    
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="meta_title">Meta title</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="meta_title" value="{{ $seo->meta_title ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="meta_description">Meta description</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="meta_description" value="{{ $seo->meta_description ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="meta_keywords">Meta Keywords</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="meta_keywords" value="{{ $seo->meta_keywords ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="og_title">Og title</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="og_title" value="{{ $seo->og_title ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="og_description">Og description</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="og_description" value="{{ $seo->og_description ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="image">Og img</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <button type="button" class="addPhotoBtn"></button>
                                    <input type="file" class="addPhoto" data-name="og_img">
                                    <div class="photoPreview">
                                        <div class="preview">
                                            <img src="{{ $seo->og_img }}">
                                            <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                            <input type="hidden" name="og_img" value="{{ $seo->og_img }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h1>Titles:</h1>
                    
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="main">Головна</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="main" value="{{ $titles->main ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="news">Новини</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="news" value="{{ $titles->news ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="contacts">Контакты</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="contacts" value="{{ $titles->contacts ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="login">Login</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="login" value="{{ $titles->login ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="search">Пошук</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="search" value="{{ $titles->search ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="reference">Довідкова інформація</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="reference" value="{{ $titles->reference ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="support">Програма підтримки</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="support" value="{{ $titles->support ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form_block">
                        <div class="fb_inside">
                            <div class="fb_label">
                                <div class="fb_label_inside">
                                    <label for="rules">Правила регулювання</label>
                                </div>
                            </div>
                            <div class="fb_input">
                                <div class="fb_input_inside">
                                    <input type="text" name="rules" value="{{ $titles->rules ?? '' }}" required>
                                </div>
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
           
        </form> --}}
    </div>

</section>
@endsection