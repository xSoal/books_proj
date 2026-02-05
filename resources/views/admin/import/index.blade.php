@extends('layouts.admin')


@section('content')

<section class="product_type main_section active">
    <div class="title_h1">
        <h1>Імпорт</h1>
    </div>

    

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
        <form action="{{ route('admin.importAdd') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }} 
            <div class="select_bg"></div>


            <div class="form_block">
                <div class="fb_inside">
                    <div class="fb_label">
                        <div class="fb_label_inside">
                            <label for="exel_file">Файл імпорту (xlsx)</label>
                        </div>
                    </div>
                    <div class="fb_input">
                        <div class="fb_input_inside">
                            <input type="file" id="exel_file" name="exel_file" value="" required>
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