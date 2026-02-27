@extends('layouts.main_page')

@section('content')



<div class="container main-layout filter">
    <aside class="sidebar">
        <h2 class="sidebar-title">{{ $translates['filters'] }}</h2>


        <form method="get" action="{{ route('search') }}" class="filter-form">
        
            {{-- <div class="filter-group">
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
            </div> --}}

            @foreach ($chars as $char)
            @if (count($char->char_vals))
                <?php
                    $has_selected_char_val = $char->char_vals->first(function($val) use ($selected_char_vals_id) {
                        return in_array($val->id, $selected_char_vals_id);
                    });
                ?>
                @if($char->is_numeric === 1)
                <div class="filter-group input__numericRange numeric" data-slug="{{ $char->translates[app()->getLocale()]->slug }}">
                    <label>{{ $char->translates[app()->getLocale()]->name }}</label>
                    <div class="range-slider-container">
                        <div class="slider-track"></div>
                        <input type="range" min="{{ $char['total_min'] }}" max="{{ $char['total_max'] }}" value="{{ isset($selected_input_range[$char->id]) ? $selected_input_range[$char->id]['cur_min'] : $char['total_min'] }}" class="slider_range_1" >
                        <input type="range" min="{{ $char['total_min'] }}" max="{{ $char['total_max'] }}" value="{{ isset($selected_input_range[$char->id]) ? $selected_input_range[$char->id]['cur_max'] : $char['total_max'] }}" class="slider_range_2" >
                    </div>
                    <div class="range-values">
                        <span class="range_1">{{ isset($selected_input_range[$char->id]) ? $selected_input_range[$char->id]['cur_min'] : $char['total_min'] }}</span>
                        <span> — </span>
                        <span class="range_2">{{ isset($selected_input_range[$char->id]) ? $selected_input_range[$char->id]['cur_max'] : $char['total_max'] }}</span>
                    </div>
                    <br>
                    <div class="input__numericRangeButtonCont">
                        <a class="btn-reset input__numericRangeButton">Ок</a>
                    </div>
                </div>
                @endif
            @endif
            @endforeach

            @foreach ($chars as $char)
            @if (count($char->char_vals))
                <?php
                    $has_selected_char_val = $char->char_vals->first(function($val) use ($selected_char_vals_id) {
                        return in_array($val->id, $selected_char_vals_id);
                    });
                ?>
                @if($char->is_numeric === 0)
                <div class="filter-group char {{ $has_selected_char_val ? 'selected' : '' }}" data-char-slug="{{ $char->translates[app()->getLocale()]->slug }}">
                    <label>{{ $char->translates[app()->getLocale()]->name }}</label>
                    <div class="checkbox-list">
                        @foreach ($char->char_vals as $char_val)
                        <?php
                            $current_val_slug = $char_val->translates[app()->getLocale()]->slug;
                            $parent_slug = $char->translates[app()->getLocale()]->slug;
                            $selected = in_array($char_val->id, $selected_char_vals_id);

                            // Получаем текущий путь и разбиваем на сегменты
                            $path = Request::path(); 
                            $path_segments = explode('/', $path);
                            
                            // Определяем "базу". Если сайт на /en/search, первыми сегментами будут ['en', 'search']
                            // Нам нужно работать только с тем, что идет ПОСЛЕ слова 'search'
                            $search_index = array_search('search', $path_segments);
                            
                            // Отрезаем базовую часть (локаль + search)
                            $base_segments = array_slice($path_segments, 0, $search_index + 1);
                            // Отрезаем фильтры
                            $filter_segments = array_slice($path_segments, $search_index + 1);

                            // Ищем индекс родительского слага в фильтрах
                            $found_index = -1;
                            foreach ($filter_segments as $idx => $segment) {
                                if (str_starts_with($segment, $parent_slug . '-')) {
                                    $found_index = $idx;
                                    break;
                                }
                            }

                            if ($selected) {
                                // ЛОГИКА УДАЛЕНИЯ
                                $current_segment = $filter_segments[$found_index];
                                $new_segment = str_replace(['-' . $current_val_slug, $current_val_slug . '-'], '', $current_segment);
                                
                                if ($new_segment === $parent_slug) {
                                    unset($filter_segments[$found_index]);
                                } else {
                                    $filter_segments[$found_index] = $new_segment;
                                }
                            } else {
                                // ЛОГИКА ДОБАВЛЕНИЯ
                                if ($found_index !== -1) {
                                    $filter_segments[$found_index] .= '-' . $current_val_slug;
                                } else {
                                    $filter_segments[] = $parent_slug . '-' . $current_val_slug;
                                }
                            }

                            // Собираем все сегменты воедино (база + измененные фильтры)
                            $all_segments = array_merge($base_segments, array_filter($filter_segments));
                            
                            // Генерируем полный URL
                            // url('/') создаст https://biblproj/, implode склеит en/search/filters...
                            $url_for_input = url(implode('/', $all_segments));

                            // Добавляем GET-параметры (сортировка и поиск), но БЕЗ параметра page
                            // (при смене фильтра логично сбрасывать пагинацию)
                            $queryParams = Request::query();
                            unset($queryParams['page']);
                            
                            if (!empty($queryParams)) {
                                $url_for_input .= '?' . http_build_query($queryParams);
                            }
    
                        ?>
                        <label>
                            <a
                                href="{{ $url_for_input }}"
                                role="checkbox"
                                aria-checked="{{ $selected ? 'true' : 'false' }}"
                                aria-label="{{ ($selected ? ($translates['choice_cancel'] ?? 'Remove filter') : ($translates['select_filter'] ?? 'Select filter')) . ': ' . ($char_val->translates[app()->getLocale()]->name ?? '') }}"
                            >
                                <span class="mp-checkbox" aria-hidden="true"></span>
                                {{ $char_val->translates[app()->getLocale()]->name }}
                            </a>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
            @endforeach
            {{-- <div class="filter-group">
                <label>Тематичні категорії</label>
                <div class="checkbox-list">
                    <label><input type="checkbox"> Історія</label>
                    <label><input type="checkbox"> Культура</label>
                    <label><input type="checkbox"> Релігія</label>
                    <label><input type="checkbox"> Архіви</label>
                </div>
            </div> --}}
            <div class="filter__resetCont">
                <a class="btn-reset" href="{{ route('search') }}">{{ $translates['reset_filters'] }}</a>
            </div>
        </form>
    </aside>

    <main id="main-content" class="content">
        <div class="results-header">
            <div class="results-count">{{ $translates['records_found'] }} {{ count($books) }}</div>
            <div class="sorting-controls">
                <label for="sort">{{ $translates['sort_by'] }}</label>
                <?php
                    $sortOptions = [
                        'name-asc' => $translates['sort_by_title'],
                    ];
                ?>
                <select id="sort" aria-label="{{ $translates['sort_by'] }}">
                    <option value="">{{ $translates['sort_by_default'] }}</option>
                    @foreach ($sortOptions as $key => $value)
                        <option value="{{ $key }}" {{ request('order') === $key ? 'selected' : ''}}>{{ $value }}</option>
                    @endforeach

                    @foreach ($chars_for_sorted_map as $key => $value )
                        <option value="{{ $key }}-asc" {{ request('order') === $key . '-asc' ? 'selected' : ''}}>{{ $value['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bottom-search-container">
            <form action="{{ url()->current() }}" method="GET" class="inline-search-form searchForm">
                
                @foreach(request()->except(['search', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
        
                <div class="search-input-wrapper">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="{{ $translates['search'] }}" 
                           aria-label="{{ $translates['search'] }}"
                           class="bottom-search-field">
                    <button type="submit" class="btn btn-primary btn-bottom-search">
                        {{ $translates['search'] }}
                    </button>
                </div>
            </form>
        </div>

        <div class="records-container">
            {{-- {{ $books }} --}}
            @foreach ($books as $item)
                <article class="record-card">
                    <div class="card-header">
                        @if($item->edition_types->isNotEmpty())
                            <div class="type-badge-container">
                                <span class="type-label">{{ $translates['type_of_publication'] ?? 'Тип видання:' }}</span>
                                <div class="badges-list">
                                    @foreach($item->edition_types as $type)
                                        @php
                                            // dd($item->id);
                                            $typeTranslation = $type->translates->firstWhere('lang', app()->getLocale());
                                        @endphp
                                        {{ $item->id }}
                                        <span class="badge badge-type">{{ $typeTranslation->name ?? '—' }}</span>
                                    @endforeach
                                </div>
                            </div>
                         @endif
                    {{-- <div class="card-actions">
                        <button class="btn-icon">Cite</button>
                        <button class="btn-icon">Export</button>
                    </div> --}}
                    </div>
                    <h3 class="record-title">
                        <a href="{{ route('book', ['slug' => $item->translates[app()->getLocale()]->slug]) }}">{{ $item->translates[app()->getLocale()]->name }}</a>
                    </h3>
                    {{-- <p class="record-author">Петренко О. В., 2024</p> --}}
                    {{-- <p class="record-details">Видавництво: Дух і Літера | DOI: 10.1234/jsiu.2024.01</p> --}}
                    @if($item->authors->isNotEmpty())
                        <div class="book-author">
                            <strong>{{ $translates['author'] ?? 'Автор' }}:</strong>
                            @foreach($item->authors as $author)
                                {{ $author->translates[app()->getLocale()]->name ?? $author->translates->first()->name ?? '' }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    @endif
                    <div class="record-tags">
                        @php
                            // 1. Получаем текущие ID тегов из URL (разделитель - дефис)
                            $currentTags = request()->filled('tag') 
                                ? array_filter(explode('-', request()->query('tag'))) 
                                : [];
                                
                            // Приводим все ID к строкам для корректного сравнения
                            $currentTags = array_map('strval', $currentTags);
                        @endphp
                    
                        @foreach($item->tags as $tag)
                            @php
                                $tagName = $tag->translates[app()->getLocale()]->name ?? null;
                                $tagId = (string)$tag->id;
                                
                                $isSelected = in_array($tagId, $currentTags);
                                
                                if ($isSelected) {
                                    $newTagsArray = array_diff($currentTags, [$tagId]);
                                } else {
                                    $newTagsArray = array_merge($currentTags, [$tagId]);
                                }
                                
                                $allParams = request()->query(); 
                    
                                if (!empty($newTagsArray)) {
                                    $allParams['tag'] = implode('-', $newTagsArray);
                                } else {
                                    unset($allParams['tag']);
                                }
                    
                                unset($allParams['page']);
                    
                                if (empty($allParams)) {
                                    $tagUrl = request()->url();
                                } else {
                                    $tagUrl = request()->url() . '?' . rawurldecode(http_build_query($allParams));
                                }
                            @endphp
                            
                            @if($tagName)
                                <a href="{{ $tagUrl }}" 
                                   class="tag {{ $isSelected ? 'active' : '' }}"
                                   title="{{ $isSelected ? $translates['choice_cancel'] : $translates['select_tag'] }}">
                                    {{ $tagName }}
                                    
                                    @if($isSelected) 
                                        <span class="remove-tag" aria-hidden="true">&times;</span> 
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>

                </article>
            @endforeach

        </div>
    </main>
</div>


@endsection