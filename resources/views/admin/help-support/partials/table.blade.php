<table class="product_listing_table">
    <thead>
        <tr>
            <th>Sr.No.</th>
            <th>Request Id</th>
            <th>Username</th>
            <th>Company</th>
            <th>Date</th>
            <th>Issue Type</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $result->request_id ?? '-' }}</td>
            <td>{{ $result->creater->name ?? '-' }}</td>
            <td>{{ $result->venderBuyer->legal_name ?? '-' }}</td>
            <td>{{ date('d/m/Y', strtotime($result->created_at)) }}</td>
            <td>{{ $result->issue_type}}</td>
            @php 
            if(strlen($result->description) > 20) {
                $description= substr($result->description, 0, 20).'<i title="'.$result->description.'" class="bi bi-info-circle-fill" aria-hidden="true"></i>';
            } else {
                $description=  $result->description;
            }
            @endphp
            <td>{!! $description !!}</td>
            @php
            $status=$result->getStatus($result->status);
            @endphp
            <td>
                <span class="badge {{$status['class']}}">{{$status['status']}}</span>
            </td>
            <td>
                <a href="{{route('admin.help_support.edit', $result->id)}}" class="btn-rfq  btn-rfq-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">No Bulk Products for Approval found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />