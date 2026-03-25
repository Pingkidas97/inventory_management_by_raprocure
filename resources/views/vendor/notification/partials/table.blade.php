<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr>
            <th>#</th>
            <th>Sender Name</th>
            <th>Date</th>
            <th>Message</th>
            <th>Action</th>
          
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($notifications->currentPage() - 1) * $notifications->perPage() + 1;
        @endphp

        @forelse ($notifications as $notification)
            <tr>
                <td>{{ $i++ }}</td> {{-- S.No. --}}
                <td>{{ $notification->sender_name ?? '' }}</td>
                <td>{{ $notification->created_at ?? '' }}</td>
                <td>{!! $notification->message ?? '' !!}</td>
                <td>
                    <a href="{{$notification->link}}" class="btn btn-sm btn-info">View</a> 
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No notification found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$notifications" />

    