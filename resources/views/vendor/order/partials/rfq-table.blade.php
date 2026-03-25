<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr> 
            <th scope="col" class="text-nowrap">Order Date</th>
            <th scope="col" class="text-nowrap">Order No</th>
            <th scope="col" class="text-nowrap">BUYER ORDER NUMBER</th>
            <th scope="col" class="text-nowrap">RFQ No </th>
            <th scope="col" class="text-nowrap">Product</th>
            <th scope="col" class="text-nowrap">Buyer Name</th>
            <th scope="col" class="text-nowrap">Order Value</th>
            <th scope="col" class="text-nowrap">Status</th>
            <th scope="col" class="text-nowrap">Action</th>
            <th scope="col" class="text-nowrap">Upload PI</th>
        </tr>
    </thead>
    <tbody>
        @php
        $order_status=['1'=>'Order Confirmed','2'=>'Order Cancelled','3'=>'Order to Approve'];
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr>
            <td>{{ date('d/m/Y', strtotime($result->created_at)) }}</td>
            <td>
                <a href="{{ route('vendor.rfq_order.show', $result->id) }}">
                {{ $result->po_number ?? '-' }}
                </a>
            </td>
            <td>{{ $result->buyer_order_number ?? '-' }}</td>
            <td>{{ $result->rfq_id ?? '-' }}</td>
            <td>{{ $result->order_variants->pluck('product.product_name')->filter()->join(', ') ?? '-' }}</td>
            <td>{{ $result->buyer->legal_name ?? '-' }}</td>
            <td>{{ $result->order_total_amount ?? '-' }}</td>
            <td>{{ $order_status[$result->order_status] ?? '-' }}</td>
            <td>
                <a class="btn-sm btn-rfq-secondary" href="{{ route('vendor.rfq_order.show', $result->id) }}"><span><i class="bi bi-eye"></i></span></a>
            </td>
            <td> 
                @php 
                $piData=orderPi($result->po_number,getParentUserId());
                @endphp
                @if(empty($piData))
                <div class="custom-file">
                    <div class="file-browse">
                        <span class="button button-browse">
                            <input onchange="validatePIFile(this)" type="file" name="pi_attachment" value=""
                                class="form-control pi-attachment-field" data-order-number="O-MGSS-25-00025/01">
                        </span>
                    </div>
                    <div class="pi-file-name-div">
                        <span class="pi-file-name d-none" title=""></span>
                    </div>
                </div>
                @else
                <a class="btn-sm btn-rfq-secondary" href="{{asset('uploads/order/'.$piData->pi_attachment)}}"><span><i class="bi bi-download"></i></span></a>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />