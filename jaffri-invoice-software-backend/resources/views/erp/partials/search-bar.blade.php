<form action="{{ $action }}" method="GET" class="search-bar" role="search">
    <span class="search-icon" data-icon="search"></span>
    <input
        type="text"
        name="search"
        value="{{ request('search') }}"
        placeholder="{{ $placeholder ?? 'Search...' }}"
        autocomplete="off"
    />
    @foreach(request()->query() as $key => $value)
        @if($key !== 'search' && $key !== 'page' && $value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
        @endif
    @endforeach
    <button type="submit" class="search-submit" title="Search" aria-label="Search">
        <span data-icon="search"></span>
    </button>
</form>
