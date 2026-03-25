<div class="table-responsive list_table_wrap">
    <table class="product_listing_table" id="productsTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>PRODUCT NAME</th>
                <th style="width: 7%;">STATUS</th>
                <th style="width: 7%;">ACTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>
                    <a href="{{ route('admin.products.index', ['id' => $product->id]) }}" style="text-decoration: none;color: #212529;">
                        {{ $product->product_name }}
                    </a>
                </td>
                <td>
                    <span>
                        <label class="switch">
                            <input type="checkbox" 
                                   id="checkbox_{{ $product->id }}" 
                                   class="product-status-toggle" 
                                   data-id="{{ $product->id }}"
                                   {{ $product->status == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-rfq btn-sm btn-rfq-secondary">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center py-4">No products found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @php
        $paginator = $products;
        $onEachSide = 2;
        $window = $onEachSide * 2;
    @endphp

    @if ($paginator->total() > 0)
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div class="mb-2">
                <small>
                    Showing
                    {{ $paginator->firstItem() ?? 0 }}
                    to
                    {{ $paginator->lastItem() ?? 0 }}
                    of
                    {{ $paginator->total() }}
                    entries
                </small>
            </div>
            <div>
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
                        </li>
                    @endif

                    {{-- Page Number Links --}}
                    @php
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

                    @if ($startPage > 1)
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                        @if ($startPage > 2)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endif

                    @for ($page = $startPage; $page <= $endPage; $page++)
                        @if ($page == $currentPage)
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                        @endif
                    @endfor

                    @if ($endPage < $lastPage)
                        @if ($endPage < $lastPage - 1)
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
                    @endif

                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </div>
        </div>
    @endif
</div>