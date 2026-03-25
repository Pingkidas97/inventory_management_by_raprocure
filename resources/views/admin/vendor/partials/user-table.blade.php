<table class="table table-bordered text-nowrap">
    <thead>
        <tr>
            <th>SN</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $key => $result)
            <tr>
                <td>{{ ++$key }}</td>
                <td>{{ $result->name ?? ''}}</td>
                <td>{{ $result->email ?? ''}}</td>
                <td>{{ $result->mobile ?? ''}}</td>
                <td>{{ $result->status == 1 ? 'Active' : 'Inactive' }}</td>
                <td>{{ $result->created_at ?? ''}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No Buyer found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

    