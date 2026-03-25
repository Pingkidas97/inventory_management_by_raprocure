
<div class="modal fade" id="forceClosureModal" tabindex="-1" aria-labelledby="forceClosureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">

            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="forceClosureModalLabel"><i class="bi bi-pencil" aria-hidden="true"></i> Force Closure</h2>
                <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
          

            <div class="modal-body">
                <form id="generate_force_closure_form">

                    <!-- Inventory Info -->
                    <div class="border rounded p-2 mb-3 bg-light">

                        <div class="mb-1">
                            <strong>Item Code :</strong>
                            <span id="fc_item_code">-</span>
                        </div>

                        <div class="mb-1">
                            <strong>Product Name :</strong>
                            <span id="fc_product_name">-</span>
                        </div>

                        <div class="mb-1">
                            <strong>Specification :</strong>
                            <span id="fc_specification">-</span>
                        </div>

                        <div>
                            <strong>Size :</strong>
                            <span id="fc_size">-</span>
                        </div>

                    </div>

                    <!-- RFQ Select -->
                    <div class="mb-3">
                        <label class="form-label"><strong>RFQ Number</strong></label>
                        <select class="form-select form-select-sm" id="rfqSelect">
                            <option value="">Select RFQ</option>
                        </select>
                    </div>

                    <!-- Order Details -->
                    <div id="rfqDetailsContent"></div>

                    <!-- Warning Message -->
                    <div id="forceClosureMessage"></div>
                    
                    <div class="d-flex justify-content-center mt-3" id="forceClosureButtonDiv" style="display:none;">
                        <button type="button"
                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11"
                            id="forceClosureButton">
                            Force Closure
                        </button>
                    </div>

                </form>
            </div>
        </div>        
    </div>
</div>

