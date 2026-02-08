@extends('layouts.main_page')

@section('content')


<main class="container">
    <a href="{{ route('search') }}" class="back-link">← Повернутися до пошуку</a>

    <div class="record-main-container">
        <div class="record-visual">
            <div class="book-placeholder">
                <span>Обкладинка</span>
            </div>
            {{-- <div class="action-buttons">
                <button class="btn-action">Як цитувати</button>
                <button class="btn-action outline">Експорт (RIS/BibTeX)</button>
            </div> --}}
        </div>

        <div class="record-info">
            {{-- <span class="material-type-label">Друкована книга, українська, 2021</span> --}}
            <h1 class="entry-title">{{ $book->translates[app()->getLocale()]->name }}</h1>
            {{-- <p class="entry-authors">Автори: <a href="#">Амос Оз</a> (Автор), <a href="#">Фанія Оз-Зальцбергер</a> (Автор)</p> --}}
            
            <div class="entry-abstract">
                <h3>Анотація</h3>
                <p>{{ $book->translates[app()->getLocale()]->anotation }}</p>
            </div>
            
            <div class="full-details">
                <h3>Деталі видання</h3>
                <br>
                <dl class="details-list">
                    @foreach ($chars as $char)
                        @foreach ($char->char_vals as $char_val)
                        <dt>
                            <a href="{{ route('search', []) }}">

                            </a>
                            {{ $char->translates[app()->getLocale()]->name }}:
                        </dt>
                        <dd>
                            <a href="{{ route('search', $char->translates[app()->getLocale()]->slug . '-' . $char_val->translates[app()->getLocale()]->slug) }}">
                                {{ $char_val->translates[app()->getLocale()]->name }}
                            </a>
                        </dd>
                        @endforeach
   
                    @endforeach
                    <dt>Теги:</dt>
                    <dd>
                        <span class="tag">Тег</span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- <aside class="record-status">
            <div class="status-card">
                <h3>Де знаходиться</h3>
                <p>Доступно в бібліотеках-партнерах:</p>
                <ul>
                    <li>Національна бібліотека України ім. В.І. Вернадського</li>
                    <li>Бібліотека НаУКМА</li>
                </ul>
                <a href="#" class="btn-full-width">Переглянути джерело</a>
            </div>
        </aside> --}}
    </div>
    <br><br><br>
</main>


@endsection