@if ($paginator->hasPages())
    <nav class="es-pagination" aria-label="Pagination">
        <div class="es-pagination-inner">
            @if ($paginator->onFirstPage())
                <span class="es-pagination-link es-pagination-link-disabled">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="es-btn es-btn-secondary es-btn-sm es-pagination-link" rel="prev">
                    {{ __('pagination.previous') }}
                </a>
            @endif

            <span class="es-pagination-info">
                Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="es-btn es-btn-secondary es-btn-sm es-pagination-link" rel="next">
                    {{ __('pagination.next') }}
                </a>
            @else
                <span class="es-pagination-link es-pagination-link-disabled">{{ __('pagination.next') }}</span>
            @endif
        </div>
    </nav>
@endif
