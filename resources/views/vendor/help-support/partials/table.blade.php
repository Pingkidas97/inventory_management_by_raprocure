<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr>
            <th scope="col" class="text-nowrap">Sr.No.</th>
            <th scope="col" class="text-nowrap">Request Id</th>
            <th scope="col" class="text-nowrap">Username</th>
            <th scope="col" class="text-nowrap">Company</th>
            <th scope="col" class="text-nowrap">Date</th>
            <th scope="col" class="text-nowrap">Issue Type</th>
            <th scope="col" class="text-nowrap">Description</th>
            <th scope="col" class="text-nowrap">Status</th>
            <th scope="col" class="text-nowrap">Actions</th>
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
                $description= substr($result->description, 0, 20).'<i title="'.$result->description.'" data-bs-toggle="tooltip" class="bi bi-info-circle-fill" aria-hidden="true"></i>';
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
                <a href="javascript:void(0);" onclick="viewrequest({{$result->id}},{{$result->created_by}});" class="ra-btn ra-btn-outline-primary-light py-2 height-inherit"><span><i class="bi bi-eye"></i></span></a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />