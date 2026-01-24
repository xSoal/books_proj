@extends('layouts.admin')


@section('content')
<section class="users_list main_section active">
    <div class="title_h1">
        <h1>Список партнерів</h1>
    </div>
    <div class="search_block">
        <div class="search_form">
            <form action="{{ route('admin.postPartners') }}" method="POST" class="for_search">
				{{ csrf_field() }}
                <div class="form_block fb_query">
                    <div class="fb_inside">
                        <div class="fb_label">
                            <div class="fb_label_inside label_search">
                                <label for="search_users">Назва</label>
                            </div>
                        </div>
                        <div class="fb_input">
                            <div class="fb_input_inside">
                                <input id="search_users" name="search" value="{{ $search }}" type="text">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form_block fb_submit">
                    <div class="fb_inside">
                        <div class="fb_input">
                            <div class="fb_input_inside">
                                <button class="btn-search" name="" value="true" type="submit">Пошук</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="add_new_item">
            <a href="{{ route('admin.addPartner') }}" class="add_item">Додати</a>
        </div>

    </div>
    <div class="table">
        <div class="thead">
            <div class="tr tr_heading">
                <div class="th number">№</div>
                <div class="th fio">Назва</div>
                <div class="th status">Статус</div>
                <div class="th date_in">Дата додавання</div>
                <div class="th edit">Ред.</div>
            </div>
        </div>
        <div class="tbody">
            @if( $items )
			    @foreach($items as  $k => $item)
                    <div class="tr_block" data-id="{{ $item->id }}" data-type="partner">
                        <div class="tr tr_values">
                            <div class="td number">{{ $page + $k + 1 }}</div>
                            <div class="td fio">{{ $item->translates['ua']['name'] }}</div>
                            <div class="td status">
                                
                                <div class="form_block_items">
                                    <div class="form_block active">
                                        <div class="fb_inside">
                                            <div class="fb_input input_toggle">
                                                <div class="fb_input_inside">
                                                    <div class="toggle {{ $item->active == 1 ? 'active' : '' }}">
                                                        <span></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="td date_in">{{ $item->created_at->format('Y-m-d H:i') }}</div>
                            <div class="td edit">
                                <a href="{{ route('admin.viewPartner', ['id' => $item->id]) }}" class="edit_link">
                                    <span>
                                        <object type="image/svg+xml" data="/images/admin/icons/edit.svg"></object>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    
    {{ $items->links() }}

</section>
@endsection