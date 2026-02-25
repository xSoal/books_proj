    
    {{-- <form class="mod-finder js-finder-searchform form-search" action="/search" method="get" role="search">
        <label for="mod-finder-searchword140" class="visually-hidden finder"></label>
        <div class="awesomplete">
            <div class="awesomplete">
                <input type="text" name="search" id="mod-finder-searchword140" class="js-finder-search-query form-control" value="{{ isset($search) ? $search : '' }}" placeholder="Пошук..." autocomplete="off" aria-autocomplete="list" aria-expanded="false" aria-owns="awesomplete_list_2" role="combobox"><ul hidden="" role="listbox" id="awesomplete_list_2" aria-label="Search Results"></ul><span class="visually-hidden" role="status" aria-live="assertive" aria-atomic="true"></span>
            </div>
                <ul hidden="" role="listbox" id="awesomplete_list_1" aria-label="Search Results"></ul>
                <span class="visually-hidden" role="status" aria-live="assertive" aria-atomic="true"></span>
            </div>
    </form> --}}

    <form class="searchForm mp-searchForm" action="{{ route('search') }}" method="GET" role="search">
        <div class="search-input-wrapper">
            <input
                type="text"
                name="search"
                class="bottom-search-field"
                placeholder="Search"
                value="{{ isset($search) ? $search : '' }}"
                aria-label="Search"
            >
            <button type="submit" class="btn btn-primary btn-bottom-search">Search</button>
        </div>
    </form>
