<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Username</th>
            <th>Designation</th>
            <th>Branch/unit</th>
            <th>Role</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse($results as $key => $user)
        <tr>
            <td>{{ $results->firstItem() + $key }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->designation }}</td>
            <td>{{ $user->branch_unit }}</td>
            <td>{{ $user->role->role_name ?? 'N/A' }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->country_code ? '+' . $user->country_code : '' }} {{ $user->mobile }}</td>
            <td>
                {{ $user->status == '1' ? 'Active' : 'Inactive' }}
            </td>
            <td>
                @if(checkPermission('MANAGE_USERS','edit','1'))
                    <a href="{{ route('buyer.user-management.edit-user', $user->id) }}" class="ra-btn small-btn ra-btn-outline-primary-light">
                        Edit
                    </a>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">No users found</td>
        </tr>
        @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />
