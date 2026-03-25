<table  class="product_listing_table">
<thead>
    <tr>
        <th>#</th>
        <th>Name Of Vendor</th>
        <th>Primary Contact</th>
        <th>Phone No</th>
        <th>Email</th>
        <th>GST No</th>
        <th>Registered Address (Address,City,State)</th>
        <th>No. of Accounts</th>
       
        <th>Total RFQ Received</th>
        <th>Total Quotation Given</th>
        <th>Total Confirmed Orders (Received)</th>
        <th>Value (Of Confirmed Orders)</th>
        <th>No. Of Verified Product</th>
        <th>Last Login Date</th>
    </tr>
</thead>
<tbody>
@forelse($vendors as $i => $vendor)
    <tr>
        <td>{{ $vendors->firstItem() + $i }}</td>
        

        <td>
            @php
                $vendorname = $vendor['vendor_name'];
            @endphp

            @if(strlen($vendorname) > 20)
                {{ Str::limit($vendorname, 20) }}
                <i class="bi bi-info-circle text-primary ms-2"
                   title="{{ $vendorname }}"
                   style="cursor: pointer;"></i>
            @else
                {{ $vendorname }}
            @endif
        </td>

        <td>{{ $vendor['primary_contact'] }}</td>
        <td>{{ $vendor['phone_no'] }}</td>
        <td>{{ $vendor['email'] }}</td>
        <td>
            @php
                $vendorgst_no = $vendor['gst_no'];
            @endphp

            @if(strlen($vendorgst_no) > 25)
                {{ Str::limit($vendorgst_no, 25) }}
                <i class="bi bi-info-circle text-primary ms-2"
                   title="{{ $vendorgst_no }}"
                   style="cursor: pointer;"></i>
            @else
                {{ $vendorgst_no }}
            @endif
        </td>

        
        @php
            $addressParts = array_filter([
                $vendor['registered_address'],
                $vendor['state'] ? "<b>{$vendor['state']}</b>" : null,
                $vendor['city'] ? "<b>{$vendor['city']}</b>" : null,
            ]);
            $fullAddress = implode(', ', $addressParts);
        @endphp

        <td>
            @if(strlen(strip_tags($fullAddress)) > 50)
                {!! Str::limit($fullAddress, 50) !!}
                <i class="bi bi-info-circle text-primary ms-2"
                   title="{!! strip_tags($fullAddress) !!}"
                   style="cursor: pointer;"></i>
            @else
                {!! $fullAddress !!}
            @endif
        </td>


        <td> {{ getTotalUserAccountsByUserId($vendor['user_id']) }}  / {{ getNoOfUsersByUserId($vendor['user_id']) }} </td>
        
        <td>{{ $vendor['total_rfq_received'] }}</td>
        <td>{{ $vendor['total_quotation'] }}</td>
        <td>{{ $vendor['total_confirmed_orders'] }}</td>
        <td>{{ number_format($vendor['value_of_confirmed_orders'], 2) }}</td>
        <td>{{ $vendor['no_of_verified_product'] }}</td>
        <td>{{ $vendor['last_login_date'] }} </td>
    </tr>
@empty
    <tr>
        <td colspan="13" class="text-center">No data available in table.</td>
    </tr>
@endforelse
</tbody>
</table>
<x-paginationwithlength :paginator="$vendors" />