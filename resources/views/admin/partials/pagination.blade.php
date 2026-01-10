@php
    use Illuminate\Pagination\LengthAwarePaginator;
@endphp

@if ($paginator instanceof LengthAwarePaginator && $paginator->hasPages())
<nav class="admin-pagination mt-4">
    <ul class="pagination justify-content-center mb-0">

        {{-- Previous --}}
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" href="{{ $paginator->previousPageUrl() ?? '#' }}">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>

        {{-- Page Numbers --}}
        @foreach ($paginator->links()->elements[0] ?? [] as $page => $url)
            <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
            </li>
        @endforeach

        {{-- Next --}}
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link" href="{{ $paginator->nextPageUrl() ?? '#' }}">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>

    </ul>
</nav>
@endif
