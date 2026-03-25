<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th width="5%">SR.NO.</th>
            <th>Role Name</th>
            <th class="text-center">Manage Permission</th>
            <th class="text-center">Status</th>
            <th class="text-center">Modified</th>
            <!-- <th width="15%">Actions</th> -->
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
             <tr>
                <td>{{ ($results->currentPage() - 1) * $results->perPage() + $loop->iteration }}</td>
                <td>{{ $result->role_name }}</td>
                <td class="text-center w-200px">
                    @if(checkPermission('MANAGE_ROLE','edit','1'))
                        <a href="{{ route('buyer.role-permission.edit-role', $result->id) }}" class="btn-sm btn-rfq btn-rfq-white width-inherit px-2 py-1 border">
                            <i class="bi bi-lock-fill"></i>
                        </a>
                    @endif
                </td>
                <td class="text-center w-100px">
                    @if(checkPermission('MANAGE_ROLE','edit','1'))
                        <span>
                            <label class="switch" for="status-{{ $result->id }}">
                                <input type="checkbox"
                                    class="status-toggle"
                                    data-id="{{ $result->id }}"
                                    id="status-{{ $result->id }}"
                                    {{ $result->is_active ? 'checked' : '' }}
                                    >
                                <span class="slider round"></span>
                            </label>
                        </span>
                    @else
                        <span>{{ $result->is_active ? 'Active' : 'Inactive' }}</span>
                    @endif
                </td>
                <td class="text-center w-100px">{{ $result->updated_at->format('d/m/Y') }}</td>
                <!--  <td>
                    <div class="action-buttons">
                        <button class="btn-rfq btn-rfq-danger btn-sm delete-btn" data-id="{{ $result->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td> -->
            </tr>
        @empty
            <tr>
                <td colspan="12">No RFQ found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

