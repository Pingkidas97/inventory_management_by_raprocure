@props(['paginator'])

@php
    $onEachSide = 2;
    $window = $onEachSide * 2;

    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();

    $startPage = max($currentPage - $onEachSide, 1);
    $endPage = min($currentPage + $onEachSide, $lastPage);

    if ($currentPage <= $onEachSide) {
        $endPage = min($window + 1, $lastPage);
    }
    if ($currentPage >= $lastPage - $onEachSide) {
        $startPage = max($lastPage - $window, 1);
    }
@endphp

@if ($paginator->total() > 0)
<div class="ra-pagination pt-3 pt-sm-0">
    <div class="row gy-3 align-items-center justify-content-between" style="width: 100%;">
        <div class="col-12 col-sm-auto">
            <form id="perPageForm" method="GET">
                <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2">
                    <div>
                        <select name="datatables-length" aria-controls="datatables" class="select-items-no" id="perPage" name="per_page">
                            @foreach ([25, 50, 75, 100] as $length)
                                <option value="{{ $length }}" {{ request('per_page', 25) == $length ? 'selected' : '' }}>
                                    {{ $length }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <label for="perPage"> entries per page</label>
                </div>
            </form>
        </div>

        <div class="col-12 col-sm-auto">
            <div class="d-flex justify-content-center justify-content-sm-end">
                <nav aria-label="Page navigation example">
                    <ul class="pagination mb-0">
                        {{-- Previous --}}
                        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
                                <span class="bi bi-chevron-left font-size-11" aria-hidden="true"></span>
                                <span class="visually-hidden-focusable">Previous</span>
                            </a>
                        </li>

                        {{-- Numbered links --}}
                        @if ($startPage > 1)
                            <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                                <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                            </li>
                            @if ($startPage > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        @for ($page = $startPage; $page <= $endPage; $page++)
                            <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                            </li>
                        @endfor

                        @if ($endPage < $lastPage)
                            @if ($endPage < $lastPage - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                            </li>
                        @endif

                        {{-- Next --}}
                        <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
                                <span class="bi bi-chevron-right font-size-11" aria-hidden="true"></span>
                                <span class="visually-hidden-focusable">Next</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endif
