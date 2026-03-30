<!---GRNToleranceModal to Modal-->
<div class="modal fade" id="grnToleranceModal" tabindex="-1" aria-labelledby="grnToleranceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form id="grnToleranceForm">
                @csrf

                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-13" id="grnToleranceModalLabel">Add GRN Tolerance</h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="buyer_id" id="buyer_id">

                    <div class="mb-3">
                        <label>Add Tolerance (%)</label>
                        <input type="number"
                               name="tolerance"
                               id="tolerance"
                               class="form-control"
                               min="1"
                               max="99"
                               required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#grnToleranceForm').on('submit', function(e) {
            e.preventDefault();

            const buyerId = "{{ (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id }}";
            const tolerance = $('#tolerance').val();

            $.ajax({
                url: savegrntoleranceUrl,
                method: 'POST',
                data: {
                    buyer_id: buyerId,
                    tolerance: tolerance,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);                        
                        $('#grnToleranceModal').modal('hide');
                    } else {
                        alert('Failed to save GRN Tolerance.');
                    }
                },
                error: function(xhr) {
                    alert('An error occurred while saving GRN Tolerance.');
                }
            });
        });
    });
</script>    