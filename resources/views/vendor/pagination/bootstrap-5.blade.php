@if ($paginator->hasPages())
<nav aria-label="Navigasi halaman">
    <ul class="pagination pagination-sm mb-0" style="gap: 2px;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" style="border-radius: 8px; border: 1px solid var(--sc-border); color: var(--sc-text-muted);">
                    <i class="ti ti-chevron-left" style="font-size: 0.8rem;"></i>
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   style="border-radius: 8px; border: 1px solid var(--sc-border); color: var(--sc-text);"
                   aria-label="@lang('pagination.previous')">
                    <i class="ti ti-chevron-left" style="font-size: 0.8rem;"></i>
                </a>
            </li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="border-radius: 8px; border: 1px solid var(--sc-border);">{{ $element }}</span>
                </li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link" style="border-radius: 8px; background: var(--sc-primary); border-color: var(--sc-primary); color: #fff; font-weight: 700;">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}"
                               style="border-radius: 8px; border: 1px solid var(--sc-border); color: var(--sc-text);">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                   style="border-radius: 8px; border: 1px solid var(--sc-border); color: var(--sc-text);"
                   aria-label="@lang('pagination.next')">
                    <i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" style="border-radius: 8px; border: 1px solid var(--sc-border); color: var(--sc-text-muted);">
                    <i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i>
                </span>
            </li>
        @endif
    </ul>
</nav>
@endif
