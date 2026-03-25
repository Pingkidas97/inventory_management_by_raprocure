<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>                
                <label class="ra-custom-checkbox">
                    <input type="checkbox" id="select-all-draft-rfq">
                    <span class="font-size-11"><b>Draft RFQ No.</b></span>
                    <span class="checkmark "></span>
                </label>
            </th>
            <th>Date</th>
            <th>Product Name</th>
            <th>Username</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
            <tr class="table-tr">
                <td>
                    <label class="ra-custom-checkbox">
                        <input type="checkbox" class="select-draft-rfq" value="{{ $result->id}}">
                        <span class="font-size-11">{{ $result->rfq_id}}</span>
                        <span class="checkmark "></span>
                    </label>
                </td>
                <td>{{ date("d/m/Y", strtotime($result->created_at))}}</td>
                <td>
                    @php
                        $products = array();
                        foreach ($result->rfqProducts as $variant) {
                            $products[] = $variant->masterProduct?->product_name;
                        }
                        $products_name = implode(', ', $products);
                    @endphp
                    <div class="d-flex">
                        <span class="rfq-product-name text-truncate">
                            {{ $products_name }}
                        </span>
                        @if (strlen($products_name) > 50)
                            <button class="btn btn-link text-black border-0 p-0 font-size-12 bi bi-info-circle-fill ms-1"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="{{ $products_name }}"></button>
                        @endif
                    </div>
                </td>
                <td>{{ $result->buyerUser->name}}</td>
                <td>
                    <div class="rfq-table-btn-group">
                        <a href="{{ route('buyer.rfq.compose-draft-rfq', $result->rfq_id) }}" class="ra-btn small-btn ra-btn-outline-primary-light">Edit</a>
                        <button class="ra-btn small-btn ra-btn-outline-danger delete-this-draft-rfq">Delete</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12">No Draft RFQ found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

    