<!---Order Details Modal-->
<div id="OrderDetailsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addRfqModalLabel"><i class="bi bi-pencil"></i> Order Details </h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRfqForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center" id="orderdetailsTable">
                            <thead>
                                <tr>
                                    <th scope="col">ORDER NO</th>
                                    <th scope="col">RFQ NO</th>
                                    <th scope="col">ORDER DATE</th>
                                    <th scope="col">ORDER Qty</th>
                                    <th scope="col">Vendor Name</th>
                                    <th scope="col">View</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>                     
                </form>
            </div>
        </div>
    </div>
</div>
<!--Order Details Modal--->