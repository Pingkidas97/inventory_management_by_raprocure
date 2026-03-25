<!---Report Modal-->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="inventoryModalLabel"><i class="bi bi-pencil"></i> All Reports</h2>
                <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="all-reports row align-items-center gy-3">
                        @if (!request()->routeIs('buyer.report.closeindent'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.closeindent') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Closed Indent</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.consume'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.consume') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Consume Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.currentStock'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.currentStock')}}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Current Stock Value</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.deadStock'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.deadStock')}}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Dead Stock </a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.grn'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.grn') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">GRN Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.getPass'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.getPass') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Gate Pass Entry Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.indent'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.indent') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Indent Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.issued'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.issued') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Issued Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.issuereturn'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.issuereturn') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Issued Return Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.manualpo'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.manualpo') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Manual PO Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.minQty'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.minQty')}}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Min Qty Reports</a></div>
                        @endif

                        @if (!request()->routeIs('buyer.report.pendingGrn'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.pendingGrn') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Pending GRN Reports</a></div>
                        @endif

                        {{-- start pingki --}}
                        @if (!request()->routeIs('buyer.report.pendingGrnStockReturn'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.pendingGrnStockReturn') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Pending GRN of Stock Return</a></div>
                        @endif
                        {{-- end pingki --}}

                        @if (!request()->routeIs('buyer.report.stockLedger'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.stockLedger') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Stock Ledger</a></div>
                        @endif
                        @if (!request()->routeIs('buyer.report.stockReturn'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.stockReturn') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Stock Return Reports</a></div>
                        @endif
                        @if (!request()->routeIs('buyer.report.workOrder'))
                        <div class="col-6 col-sm-4"><a href="{{ route('buyer.report.workOrder') }}" class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-13 py-3">Work Order Reports</a></div>
                        @endif
                        <div class="col-6 col-sm-4">&nbsp;</div>
                        @if (!request()->routeIs('buyer.inventory.index'))
                        <div class="col-6 col-sm-4">&nbsp;</div>
                        @endif

                </div>
            </div>
        </div>
    </div>
</div>
<!---Report Modal-->
