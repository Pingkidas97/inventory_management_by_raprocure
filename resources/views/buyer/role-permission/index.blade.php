@extends('buyer.layouts.app', ['title'=>'Manage Role'])

@section('css')
<link rel="stylesheet" href="{{ asset('public/assets/buyer/css/toggal.css') }}" />
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="bg-white favourite-vendor user-manage-role">
                <div class="card-head-line">
                    <h3>Manage Role</h3>
                    @if(checkPermission('MANAGE_ROLE','add','1'))
                        <a href="{{route('buyer.role-permission.create-role')}}" class="btn ra-btn ra-btn-primary small-btn">+ Add New Role</a>
                    @endif
                </div>
                <div class="table-responsive">
                      @include('buyer.role-permission.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script>
     // Status toggle handler
    $('.status-toggle').on('change', function() {
        const roleId = $(this).data('id');
        const isActive = $(this).is(':checked');

        $.ajax({
            url: "{{ route('buyer.role-permission.status', ['id' => ':id']) }}".replace(':id', roleId),
            type: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                is_active: isActive ? 1 : 0
            },
            beforeSend: function() {
                $(`#status-${roleId}`).prop('disabled', true);
            },
            success: function(response) {
                toastr.success(response.message || 'Status updated successfully');
            },
            error: function(xhr) {
                // Revert the toggle on error
                $(`#status-${roleId}`).prop('checked', !isActive);
                toastr.error(xhr.responseJSON?.message || 'Error updating status');
            },
            complete: function() {
                $(`#status-${roleId}`).prop('disabled', false);
            }
        });
    });
</script>
@endsection
