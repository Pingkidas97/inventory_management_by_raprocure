<table class="product_listing_table">
    <thead>
        <tr>
            <th>Buyer Code</th>
            <th>Buyer Name</th>
            <th>Primary Contact</th>
            <th>Buyer Email</th>
            <th>Date Of Verification</th>
            <th>Buyer Contact</th>
            {{-- <th>Profile Status</th> --}}
            <th>Status</th>
            <th>Inventory Enable</th>
            <th>API Enable</th>
            <th>Currency</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
            <tr>
                <td>{{ $result->buyer_code ?? ''}}</td>
                <td>
                    <a href="{{ route('admin.buyer.user', $result->user_id) }}" style="color: blue;text-decoration: underline !important;"> 
                     {{ $result->legal_name ?? ''}} 
                    </a> 
                    <a href="#" style="margin-left: 5px; color: inherit; text-decoration: none;">
                      <i class="bi bi-pencil-square" style="cursor: pointer;"></i>
                    </a>
                </td>
                <td>{{ $result->users->name ?? ''}}</td>
                <td>{{ $result->users->email ?? ''}}</td>
                <td>{{ optional($result->buyerVerifiedAt()->first())->start_date ? date("d/m/Y", strtotime($result->buyerVerifiedAt()->first()->start_date)) : ''}}</td>
                <td>{{ !empty($result->users->country_code) ? '+'.$result->users->country_code : '' }} {{ $result->users->mobile ?? ''}}</td>
                {{-- <td>
                    <span>
                        <label class="switch">
                            <input type="checkbox"
                                   class="profile-status-toggle"
                                   data-id="{{ $result->id }}"
                                   {{ !empty($result->users) && $result->users->is_verified == 1 ? 'checked' : '' }}
                                   onchange="changeProfileStatus('{{ $result->id }}', this.checked ? 1 : 2);">
                            <span class="slider round"></span>
                        </label>
                    </span>
                   <!--  <select class="form-select" id="profile_status" name="profile_status" onchange="changeProfileStatus('{{ $result->id }}',this.value);">
                        <option value="1" {{ !empty($result->users) && $result->users->is_verified == 1 ? 'selected' : '' }}>Verified</option>
                        <option value="2" {{ !empty($result->users) && $result->users->is_verified == 2 ? 'selected' : '' }}>Not Verified</option>
                    </select> -->
                </td> --}}
                <td>
                    <span>
                        <label class="switch">
                            <input type="checkbox"
                                   class="status-toggle"
                                   data-id="{{ $result->id }}"
                                   {{ !empty($result->users) && $result->users->status == 1 ? 'checked' : '' }}
                                   onchange="changeStatus('{{ $result->id }}', this.checked ? 1 : 2);">
                            <span class="slider round"></span>
                        </label>
                    </span>
                    <!-- <select class="form-select" id="status" name="status" onchange="changeStatus('{{ $result->id }}',this.value);">
                        <option value="1" {{ !empty($result->users) && $result->users->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="2" {{ !empty($result->users) && $result->users->status == 2 ? 'selected' : '' }}>Inactive</option>
                    </select> -->
                </td>
                <td>
                   <span>
                        <label class="switch">
                            <input type="checkbox"
                                   class="inventory-status-toggle"
                                   data-id="{{ $result->id }}"
                                   {{ !empty($result->users) && $result->users->is_inventory_enable == 1 ? 'checked' : '' }}
                                   onchange="changeInventoryStatus('{{ $result->id }}', this.checked ? 1 : 0);">
                            <span class="slider round"></span>
                        </label>
                    </span>
                   <!--  <select class="form-select" id="inventory_status" name="inventory_status" onchange="changeInventoryStatus('{{ $result->id }}',this.value);">
                        <option value="1" {{ !empty($result->users) && $result->users->is_inventory_enable == 1 ? 'selected' : '' }}>Enable</option>
                        <option value="0" {{ !empty($result->users) && $result->users->is_inventory_enable == 0 ? 'selected' : '' }}>Disable</option>
                    </select> -->
                </td>
                <td>
                    <span>
                        <label class="switch">
                            <input type="checkbox"
                                   class="api-status-toggle"
                                   data-id="{{ $result->id }}"
                                   {{ !empty($result->users) && $result->users->is_api_enable == 1 ? 'checked' : '' }}
                                   onchange="changeApiStatus('{{ $result->id }}', this.checked ? 1 : 0);">
                            <span class="slider round"></span>
                        </label>
                    </span>
                    <!-- <select class="form-select" id="api_status" name="api_status" onchange="changeApiStatus('{{ $result->id }}',this.value);">
                        <option value="1" {{ !empty($result->users) && $result->users->is_api_enable == 1 ? 'selected' : '' }}>Enable</option>
                        <option value="0" {{ !empty($result->users) && $result->users->is_api_enable == 0 ? 'selected' : '' }}>Disable</option>
                    </select> -->
                </td>
                <td>
                    <select class="form-select" id="currency" name="currency" onchange="changeCurrency('{{ $result->id }}',this.value);">
                        <option value="">Assign Currency</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ !empty($result->users) && $result->users->currency == $currency->id ? 'selected' : '' }}>{{ $currency->currency_name }} ({{ $currency->currency_symbol }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <span style="display: flex;gap: 5px;">
                        <a href="{{ route('admin.buyer.profile', $result->user_id) }}" class="btn-rfq btn-rfq-primary btn-sm store-profile">Profile</a>
                        <a href="{{ route('admin.buyer.plan', $result->id) }}" class="btn-rfq btn-rfq-secondary btn-sm {{ empty($result->buyer_code) ? "disabled" : "" }}">Plan</a> 
                        @if(empty($result->buyer_code))
                            <a type="button" class="btn-rfq btn-sm btn-rfq-danger {{ $result->users->status==1 ? 'd-none' : '' }} buyer-delete-{{ $result->id }}" href="javascript:void(0)" 
                            onclick="deleteBuyer(this, '{{ $result->user_id }}', '{{$result->legal_name}}');">Delete</a>
                        @endif
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12">No Buyer found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

    