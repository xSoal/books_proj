@extends('layouts.main_page')

@section('content')
{{-- <div class="container search">
    <div class="container-inner">
        <div class="row">
            <p class="searchQueries">Search queries</p>
            @include('main_page.components.search')
            @if(!count($resultSearch))
                <h1>No results</h1>
            @endif
            <div class="search-results">
                <ul id="search-result-list" class="search-results list-striped js-highlight com-finder__results-list">
                @foreach ($resultSearch as $item)
                    <li>
                        <h4 class="result-title">
                            <a href="/{{ $item->type }}/{{ $item->slug }}">
                                {{ $item->title }} 
                            </a>
                        </h4>
                        <p class="result-text">
                            {{ str(strip_tags($item->content))->limit(250) }}
                        </p>
                    </li>
                @endforeach
                </ul>
            </div>
        </div>

        
        {{ $resultSearch->links() }}
    </div>
</div> --}}

<div class="container main-layout">
    <aside class="sidebar">
        <h2 class="sidebar-title">Фільтри</h2>
        <form class="filter-form">
            <div class="filter-group">
                <label>Рік публікації (1991-2025)</label>
                <div class="range-inputs">
                    <input type="number" placeholder="Від" min="1991" max="2025">
                    <input type="number" placeholder="До" min="1991" max="2025">
                </div>
            </div>

            <div class="filter-group">
                <label>Тип матеріалу</label>
                <select>
                    <option value="">Всі типи</option>
                    <option value="book">Книга</option>
                    <option value="article">Стаття</option>
                    <option value="section">Розділ книги</option>
                    <option value="collection">Збірник</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Тематичні категорії</label>
                <div class="checkbox-list">
                    <label><input type="checkbox"> Історія</label>
                    <label><input type="checkbox"> Культура</label>
                    <label><input type="checkbox"> Релігія</label>
                    <label><input type="checkbox"> Архіви</label>
                </div>
            </div>

            <button type="reset" class="btn-reset">Скинути фільтри</button>
        </form>
    </aside>

    <main class="content">
        <div class="results-header">
            <div class="results-count">Знайдено записів: 5,420</div>
            <div class="sorting-controls">
                <label for="sort">Сортувати за:</label>
                <select id="sort">
                    <option>Роком</option>
                    <option>Автором</option>
                    <option>Алфавітом</option>
                    <option>Типом публікації</option>
                </select>
            </div>
        </div>

        <div class="records-container">
            <article class="record-card">
                <div class="card-header">
                    <span class="badge">Книга</span>
                    <div class="card-actions">
                        <button class="btn-icon">Cite</button>
                        <button class="btn-icon">Export</button>
                    </div>
                </div>
                <h3 class="record-title">
                    <a href="#">Єврейська спадщина України: бібліографічний покажчик</a>
                </h3>
                <p class="record-author">Петренко О. В., 2024</p>
                <p class="record-details">Видавництво: Дух і Літера | DOI: 10.1234/jsiu.2024.01</p>
                <div class="record-tags">
                    <span class="tag">Бібліографія</span>
                    <span class="tag">Незалежна Україна</span>
                </div>
            </article>

            <article class="record-card">
                <span class="badge">Стаття</span>
                <h3 class="record-title">
                    <a href="#">Дослідження юдаїки в університетах України (1991-2025)</a>
                </h3>
                <p class="record-author">Іваненко І. І., 2022</p>
                <p class="record-details">Часопис: Юдаїка сьогодні | ISSN: 2222-1111</p>
                <div class="record-tags">
                    <span class="tag">Освіта</span>
                </div>
            </article>
        </div>
    </main>
</div>


@endsection