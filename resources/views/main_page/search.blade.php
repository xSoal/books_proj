@extends('layouts.main_page')

@section('content')
<div class="container search">
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
</div>


@endsection