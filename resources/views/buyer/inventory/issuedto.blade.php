<!---Issue to Modal-->
<div class="modal fade" id="issuedtoModal" tabindex="-1" aria-labelledby="issuedtoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="inventoryModalLabel"><i class="bi bi-pencil" aria-hidden="true"></i> &nbsp;Issued To</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="issue_to_data_form">
                    @csrf
                    <!-- <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">
                            <span class="bi bi-save font-size-11" aria-hidden="true"></span> Add</button>
                    </div> -->
                    <!-- Responsive Table -->
                    <div class="table-responsive">
                        <div class="d-flex justify-content-end my-3">
                            <button type="button" data-btn-type="2" class="save-form-btn ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11" id="add_more_issue_to">
                                <i class="bi bi-save font-size-11" aria-hidden="true"></i> Add
                            </button>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="product-listing-table w-100 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Sr. No.</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="issue_to_details_response">
                                    {{-- <tr class="issueToRow"> --}}
                                        {{-- <td id="IssueToRow_1">1</td>
                                        <td><input type="text" class="form-control first_issue_to_name" name="issue_to_name[1]" id="issuedto"></td>
                                        <td></td>
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <input type="hidden" id="add_more_isssueto_counter" value="1">
                    <!-- Save Button -->
                    <div class="d-flex justify-content-center mt-3">
                        <button type="submit" id="save_issue_to_button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_issuedto"><i class="bi bi-save"></i>Save Issued To</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


