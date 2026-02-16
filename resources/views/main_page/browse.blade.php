@extends('layouts.main_page')

@section('content')



{{-- <div class="container main-layout filter">
    <main class="content">
        <div class="results-header">
            <div class="results-count">Знайдено записів: {{ count($books) }}</div>
            <div class="sorting-controls">
                <label for="sort">Сортувати за:</label>
                <?php
                    $sortOptions = [
                        'name-asc' => 'Алфавітом',
                    ];
                ?>
                <select id="sort">
                    <option value="">За замовчуванням</option>
                    @foreach ($sortOptions as $key => $value)
                        <option value="{{ $key }}" {{ request('order') === $key ? 'selected' : ''}}>{{ $value }}</option>
                    @endforeach
                    @foreach ($chars_for_sorted_map as $key => $value )
                        <option value="{{ $key }}-desc" {{ request('order') === $key . '-desc' ? 'selected' : ''}}>{{ $value['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="records-container">
            @foreach ($books as $item)
                <article class="record-card">
                    <div class="card-header">
                        <span class="badge">Книга</span>
  
                    </div>
                    <h3 class="record-title">
                        <a href="{{ route('book', ['slug' => $item->translates[app()->getLocale()]->slug]) }}">{{ $item->translates[app()->getLocale()]->name }}</a>
                    </h3>
                    <div class="record-tags">
                        <span class="tag">Тут будуть теги</span>
                        <span class="tag">Бібліографія</span>
                        <span class="tag">Незалежна Україна</span>
                    </div>
                </article>
            @endforeach
        </div>
    </main>
</div> --}}


<div class="container main-layout browse-page"> 
    <main class="content">
        <div class="results-header">
            <div class="results-count">{{ $translates['records_found'] }} {{ $books->total() }}</div>
            <div class="sorting-controls">
                <label for="sort">{{ $translates['sort_by'] }}</label>
                <?php
                    $sortOptions = [
                        'name-asc' => $translates['sort_by_title'],
                    ];
                ?>
                <select id="sort">
                    <option value="">{{ $translates['sort_by_default'] }}</option>
                    @foreach ($sortOptions as $key => $value)
                        <option value="{{ $key }}" {{ request('order') === $key ? 'selected' : ''}}>{{ $value }}</option>
                    @endforeach
                    @foreach ($chars_for_sorted_map as $key => $value )
                        <option value="{{ $key }}-desc" {{ request('order') === $key . '-desc' ? 'selected' : ''}}>{{ $value['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="browse-grid">
            @foreach ($books as $item)
                @php
                    $locale = app()->getLocale();
                    $translate = $item->translates[$locale] ?? $item->translates['uk']; // фолбек на укр
                @endphp
                <article class="browse-card">
                    <div class="card-image">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $translate->name }}">
                        @else
                            <div class="card-placeholder">
                                <span>{{ mb_substr($translate->name, 0, 1) }}</span>
                            </div>
                        @endif
                        {{-- <span class="card-badge">{{ $item->type ?? 'Книга' }}</span> --}}
                    </div>

                    <div class="card-content">
                        <h3 class="card-title">
                            <a href="{{ route('book', ['slug' => $translate->slug]) }}">
                                {{ $translate->name }}
                            </a>
                        </h3>
                        
                        {{-- <p class="card-author">
                            {{ $item->author ?? 'Автор не вказаний' }} 
                            @if($item->year) <span class="card-year">({{ $item->year }})</span> @endif
                        </p> --}}

                        <p class="card-excerpt">
                            {{ Str::limit($item->translates[app()->getLocale()]->anotation ?? $translates['have_not_anotation'], 150) }}
                        </p>

                        <div class="card-footer">
                            <a href="{{ route('book', ['slug' => $translate->slug]) }}" class="btn-more">{{ $translates['read_more'] }} →</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        
        <div class="pagination-container">
            {{ $books->links() }}
        </div>
    </main>
</div>


@endsection