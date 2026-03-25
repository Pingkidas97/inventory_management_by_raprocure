
<div class="modal fade" id="workOrderModal" tabindex="-1" aria-labelledby="workOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="workOrderModalLabel"><i class="bi bi-pencil" aria-hidden="true"></i> Generate Work Order</h2>
                <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="generate_work_order_form">
                     <div class="row search-vendor-name position-relative">
                        <div class="col-12 col-sm-auto">
                            <label for="vendor_name" class="fw-bold manualpo-label">Vendor Name: <sup class="text-danger">*</sup></label>
                        </div>
                        <div class="col-12 col-sm-8">
                            <div class="position-relative">
                                <input type="text" class="form-control mb-2" name="vendor_name" id="wo_vendor_name" autocomplete="off" placeholder="Vendor Name" />
                                <input type="hidden" name="vendor_user_id" id="wo_vendor_user_id" />

                                <div class="search_text_box shadow rounded manualPOdropdown-menu show" id="vendorSuggestionsWo" style="display: none;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto">
                            <label for="date" class="fw-bold manualpo-label">Work Order Date: <sup class="text-danger">*</sup></label>
                        </div>
                        <div class="col-12 col-sm-auto text-end">
                            <input type="text" class="form-control mb-2" name="wo_created_date" id="wo_created_date" autocomplete="off" value="{{ now()->format('d/m/Y') }}" />
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table border mb-4 vendorDetails">
                            <tr id="vendorNameIdWo">
                            </tr>
                        </table>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="product-listing-table w-100" id="forworkOrderInventoryDetailsTable">
                            <thead>
                                <tr>
                                    <input type="hidden" name="branch_id" id="modal_branch_id">
                                    <th class="text-nowrap text-center align-middle">Product Description</th>
                                    <th class="text-nowrap text-center align-middle">Price (<span class="mo_currency_symbol"></span>) <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">MRP (<span class="mo_currency_symbol"></span>) <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">Disc.(%) <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">GST <span class="text-danger">*</span></th>
                                    <th class="text-nowrap text-center align-middle">Total Amount(<span class="mo_currency_symbol"></span>)</th>
                                    <th><button type="button" id="wo_add_row" class="btn btn-primary mb-2 float-right"> + </button></th>
                                </tr>
                            </thead>
                            <tbody id="forworkOrderInventoryDetailsTableBody">
                                <!-- Dynamic rows will be appended here -->
                            </tbody>

                        </table>
                    </div>
                    <div class="border_hr"></div>
                    <div class="row g-3 pt-5 pt-sm-5 mb-3">
                        <!-- Payment Terms -->
                        <div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-journal-medical"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="wo_paymentTerms" name="paymentTerms" placeholder="Payment Terms"  maxlength="2000">
                                    <label>Payment Terms <sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Period -->
                        <!--<div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="wo_deliveryPeriod" name="deliveryPeriod" placeholder="Delivery Period" maxlength="3">
                                    <label>Delivery Period (In Days)<sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>-->

                        <!-- Price Basis -->
                        <!--<div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-currency-rupee"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="wo_priceBasis"  maxlength="2000" name="priceBasis" placeholder="Price Basis">
                                    <label>Price Basis <sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>-->
                        <!-- Currency -->
                        <div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-currency-rupee"></span></span>
                                <div class="form-floating">
                                    <select name="currency_id" id="wo_currency_id" class="form-control">

                                    </select>
                                    <label>Currency <sup class="text-danger">*</sup></label>
                                </div>
                            </div>
                        </div>
                        <!-- Remarks and Additional Remarks -->
                        <div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-pencil"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control specialCharacterAllowed" placeholder="Remarks" id="wo_remarks" name="remarks" maxlength="3000">
                                    <label>Remarks</label>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Remarks -->
                        <div class="col-12 col-sm-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><span class="bi bi-pencil"></span></span>
                                <div class="form-floating">
                                    <input type="text" class="form-control specialCharacterAllowed" placeholder="Additional Remarks" id="wo_additionalRemarks" name="additionalRemarks" maxlength="3000">
                                    <label>Additional Remarks</label>
                                </div>
                            </div>
                        </div>
                        @php
                            $last_other_terms = DB::table('other_terms_conditions')
                                ->where('buyer_id', getParentUserId())
                                ->orderBy('id', 'desc')
                                ->first();

                            // Use database value if exists, otherwise read from text file
                            if (!empty($last_other_terms)) {
                                $other_terms_condition = $last_other_terms->other_terms;
                            } else {
                                $other_terms_condition = file_get_contents(public_path('assets/buyer/other_terms_condition/terms.txt'));
                            }
                        @endphp
                        <!-- Other Terms and Conditions -->
                        <div class="col-12 col-sm-12 mb-3">
                            <input type="checkbox" id="wo_other_term_check" value="1" checked="" name="other_term_check" title="Unchecked removes other terms and condition" />
                            <label style="color:#015294;" for="other_term_check">Other Terms and Condition</label>
                            <textarea id="other_terms_textarea" name="other_terms_textarea" rows="10" maxlength="15600" style="width:100%; padding:5px;">{{ $other_terms_condition }}</textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11" id="generate_work_order_product">
                            <i class="bi bi-save font-size-11"></i> Generate Work Order
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('exJs')
    <script>
        // const currencySymbol = "{{ session('user_currency.symbol', '₹') }}";
        $('#wo_created_date').datetimepicker({
            format: 'd/m/Y',
            timepicker: false,
            maxDate: 0            
        });
    </script>
@endpush
