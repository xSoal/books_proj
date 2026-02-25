@extends('layouts.main_page')

@section('content')

<link rel="stylesheet" href="{{ asset('js/swiper-bundle.min.css') }}" />
<script src="{{ asset('js/swiper-bundle.min.js') }}"></script>


<main id="main-content" class="container">
    <nav class="back-links" aria-label="{{ $translates['navigation'] ?? 'Навігація' }}">
        <a href="{{ route('search') }}" class="back-link">← {{ $translates['to_search'] }}</a>
        <a href="{{ route('browse') }}" class="back-link">← {{ $translates['to_browse'] }}</a>
    </nav>

    <div class="record-main-container">
        <div class="record-visual">
            <div class="book-placeholder">
                @if($book->img)
                    <img src="{{ $book->img }}" 
                         alt="{{ $book->translates[app()->getLocale()]->name }}" 
                         class="book-cover-img">
                @else
                    <span>{{ $translates['book_image'] }}</span>
                @endif
            </div>
            <button type="button" class="btn-feedback-trigger">
                {{ $translates['feedback'] ?? 'Зворотній звʼязок' }}
            </button>
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
                <h3>{{ $translates['adnotation'] }}:</h3>
                <p>{{ $book->translates[app()->getLocale()]->anotation }}</p>
            </div>
            
            <div class="full-details">
                <h3>{{ $translates['book_details'] }}</h3>
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
                    <dt>{{ $translates['tags'] }}:</dt>
                    <dd class="record-tags book-page-tags">
                        @php
                            $currentTags = request()->filled('tag') 
                                ? array_filter(explode('-', request()->query('tag'))) 
                                : [];
                                
                            $currentTags = array_map('strval', $currentTags);
                        @endphp
                    
                        @foreach($book->tags as $tag)
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
                                    $tagUrl = route('search');
                                } else {
                                    $tagUrl = route('search') . '?' . rawurldecode(http_build_query($allParams));
                                }
                            @endphp
                            
                            @if($tagName)
                                <a href="{{ $tagUrl }}" 
                                   class="tag {{ $isSelected ? 'active' : '' }}"
                                   title="{{ $isSelected ?  $translates['choice_cancel'] :  $translates['select_tag']  }}">
                                    {{ $tagName }}
                                    
                                    @if($isSelected) 
                                        <span class="remove-tag" aria-hidden="true">&times;</span> 
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </dd>
                </dl>
            </div>
        </div>

        <aside class="record-sidebar">
            @if($otherBooks->count() > 0)
                <div class="sidebar-widget">
                    <h3 class="sidebar-title">{{ $translates['other_works_author'] }}</h3>
                    
                    <div class="swiper author-works-swiper">
                        <div class="swiper-wrapper">
                            @foreach ($otherBooks as $otherBook)
                                @php 
                                    $otherTranslate = $otherBook->translates[app()->getLocale()] ?? $otherBook->translates['uk'];
                                @endphp
                                <div class="swiper-slide">
                                    <a href="{{ route('book', ['slug' => $otherTranslate->slug]) }}" class="sidebar-book-card">
                                        <div class="sidebar-book-img">
                                            <span>{{ mb_substr($otherTranslate->name, 0, 1) }}</span>
                                        </div>
                                        <div class="sidebar-book-content">
                                            <h4 class="sidebar-book-name">{{ Str::limit($otherTranslate->name, 55) }}</h4>
                                            <span class="sidebar-book-meta">{{ $otherBook->year }}</span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
        
                    <div class="sidebar-widget-footer">
                        @if($otherBooks->count() > 3)
                        <div class="sidebar-nav-wrapper">
                            <button type="button" class="nav-btn-arrow prev-author" aria-label="Previous">←</button>
                            <div class="swiper-pagination-custom"></div>
                            <button type="button" class="nav-btn-arrow next-author" aria-label="Next">→</button>
                        </div>
                        @endif
                        
                        {{-- <a href="{{ route('search', ['author' => $book->author]) }}" class="sidebar-all-link">
                            Всі праці автора <span class="arrow">→</span>
                        </a> --}}
                    </div>
                </div>
            @endif
        </aside>

        
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
    <div id="feedbackModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="feedbackModalTitle" aria-hidden="true">
        <div class="modal-content" tabindex="-1">
            <div class="modal-header">
                <h3 id="feedbackModalTitle" class="modal-title">{{ $translates['contact_us'] }}</h3>
                <button type="button" class="modal-close" aria-label="{{ $translates['close'] ?? 'Закрити' }}">&times;</button>
            </div>
            
            <form action="#" method="POST" class="feedback-form">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <div class="form-group">
                    <label for="email">{{ $translates['contact_email'] }}</label>
                    <input type="email" id="email" name="email" required autocomplete="email" class="form-control" placeholder="{{ $translates['contact_email'] }}">
                </div>
                <div class="form-group">
                    <label>{{ $translates['about_book'] }}</label>
                    <input disabled class="form-control" value="{{ $book->translates[app()->getLocale()]->name }}">
                </div>
                <div class="form-group">
                    <label for="message">{{ $translates['message'] }}</label>
                    <textarea class="feed_back_textArea" name="message" id="message" rows="8" placeholder="{{ $translates['message'] }}"></textarea>
                </div>
                <button type="submit" class="btn-submit-feedback">{{ $translates['contact_send_message'] }}</button>
            </form>
        </div>
    </div>
    <br><br><br>
</main>


@endsection