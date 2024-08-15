@if ($paginator->hasPages())
    <ul>
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true"><span>&lt;</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}">&lt;</a></li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}">&gt;</a></li>
        @else
            <li class="disabled" aria-disabled="true"><span>&gt;</span></li>
        @endif
    </ul>

@endif
