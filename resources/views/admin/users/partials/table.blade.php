<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $key => $user)
            <tr>
                <td>{{ $users->firstItem() + $key }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->country_code ? '+' . $user->country_code : '' }} {{ $user->mobile }}</td>
                <td>{{ $user->role->role_name ?? 'N/A' }}</td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input status-toggle" 
                               type="checkbox" 
                               data-id="{{ $user->id }}"
                               id="status-{{ $user->id }}" 
                               {{ $user->status == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status-{{ $user->id }}"></label>
                    </div>
                </td>
                <td>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-rfq btn-rfq-secondary btn-sm">
                         Edit
                    </a>

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No users found</td>
            </tr>
        @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$users" />
