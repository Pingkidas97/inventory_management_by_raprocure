<table class="product_listing_table">
    <thead>
        <tr>
            <th>Vendor Code</th>
            <th>Vendor Name</th>
            <th>Primary Contact</th>
            <th>Vendor Email</th>
            <th>Date Of Verification</th>
            <th>No Of Verified Products</th>
            <th>Vendor Contact</th>
            <th>Add Vendor Product</th>
            <th>Vendor Web</th>
            <th>Profile Status</th>
            <th>Status</th>
            <th>View Vendor Profile & Send Plan</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
            <tr>
                <td>{{ $result->vendor_code ?? ''}}</td>
                <td>
                   {{-- <a href="{{ route('admin.vendor.user', $result->user_id) }}"> 
                      {{ $result->user->name ?? ''}}
                    </a> --}}

                    <a href="javascript:void(0)"> 
                        {{ $result->legal_name ?? ''}}
                    </a>
                    <a href="javascript:void(0)" style="margin-left: 5px; color: inherit; text-decoration: none;">
                        <i class="bi bi-pencil-square" style="cursor: pointer;"></i>                    
                    </a>
                    @if($result->t_n_c == 1 && empty($result->vendor_code))
                        <img class="pending-imgs" src="{{ asset('public/assets/superadmin/images/pending-img.png') }}">
                    @endif
                </td>
                <td>{{ $result->user->name ?? ''}} </td>
                <td>{{ $result->user->email ?? ''}}</td>
                <td>{{ optional($result->vendorVerifiedAt()->first())->start_date ? date("d/m/Y", strtotime($result->vendorVerifiedAt()->first()->start_date)) : ''}}</td>
                <td>{{ $result->vendor_products_count ?? ''}}</td>
                <td>{{ !empty($result->user->country_code) ? '+'.$result->user->country_code : '' }} {{ $result->user->mobile ?? ''}}</td>
                <td>
                    @if(!empty($result->vendor_code) && $result->user->status == 1)
                    <a href="{{ route('admin.vendor.products.create', $result->user_id) }}" class="btn-rfq btn-rfq-secondary btn-sm vendor-product-btn" style="padding: 1px 8px;">+PRD</a>
            	    <a href="{{ route('admin.vendor.products.bulk_create', $result->user_id) }}" class="btn-rfq btn-rfq-secondary btn-sm vendor-product-btn" style="padding: 1px 8px;">++PRD</a>
                    @endif
                </td>
                <td>
                    @if(!empty($result->vendor_code) && $result->user->is_verified == 1)
                    <a href="javascript:void(0);" class="btn-rfq btn-rfq-secondary web-page" style="padding: 1px 12px;">Vendor Web</a>
                    @endif
                </td>
                <td>
                    <span>
                        <label class="switch">
                            <input type="checkbox"
                                   class="profile-status-toggle"
                                   data-id="{{ $result->user_id }}"
                                   {{ $result->user->is_verified == 1 ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </span>

                   <!--  <select class="form-select" id="profile_status" name="profile_status" onchange="changeProfileStatus('{{ $result->id }}',this.value);">
                        <option value="1" {{ $result->user->is_verified == 1 ? 'selected' : '' }}>Verified</option>
                        <option value="2" {{ $result->user->is_verified == 2 ? 'selected' : '' }}>Not Verified</option>
                    </select> -->
                </td>
                <td>
                      <span>
                            <label class="switch">
                                <input type="checkbox" class="status-toggle"
                                       data-id="{{ $result->id }}"
                                       {{ $result->user->status == 1 ? 'checked' : '' }} />
                                <span class="slider round"></span>
                            </label>
                        </span>

                    <!-- <select class="form-select" id="status" name="status" onchange="changeStatus('{{ $result->id }}',this.value);">
                        <option value="1" {{ $result->user->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="2" {{ $result->user->status == 2 ? 'selected' : '' }}>Inactive</option>
                    </select> -->
                </td>
                <td>
                    <span style="display: flex;gap: 5px;">
                        <a href="{{ route('admin.vendor.profile', $result->user_id ) }}" class="btn-rfq btn-rfq-primary btn-sm store-profile">Profile</a>
                        <a href="{{ route('admin.vendor.plan', $result->id) }}" class="btn-rfq btn-rfq-secondary btn-sm {{ empty($result->vendor_code) ? "disabled" : "" }}">Plan</a>
                        @if(empty($result->vendor_code))
                            <a type="button" class="btn-rfq btn-sm btn-rfq-danger {{ $result->user->status==1 ? 'd-none' : '' }} vendor-delete-{{ $result->id }}" href="javascript:void(0)" 
                            onclick="deleteVendor(this, '{{ $result->user_id }}', '{{$result->legal_name}}');">Delete</a>
                        @endif
                        @if(!empty($result->user->user_created_by))
                            <a href="{{route('admin.vendor.sa-vendor-profile', $result->user_id)}}" target="_blank" class="btn-rfq btn-rfq-secondary btn-sm">Edit</a>
                        @endif
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12">No Vendor found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

    