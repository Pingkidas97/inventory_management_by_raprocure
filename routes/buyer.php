<?php
use App\Http\Controllers\Buyer\BuyerDashboardController;
use App\Http\Controllers\Buyer\BuyerProfileController;
use App\Http\Controllers\Buyer\CommonController;
use App\Http\Controllers\Buyer\CategoryController;
use App\Http\Controllers\Buyer\VendorProductController;
use App\Http\Controllers\Buyer\RFQDraftController;
use App\Http\Controllers\Buyer\RFQComposeController;
use App\Http\Controllers\Buyer\ComposeRFQController;
//start inventory Section
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Buyer\InventoryController;
use App\Http\Controllers\Buyer\InventoryVendorProductController;
use App\Http\Controllers\Buyer\BulkInventoryController;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\WorkOrderController;
use App\Http\Controllers\Buyer\ForceClosureController;
use App\Http\Controllers\Buyer\ReportsController;
use App\Http\Controllers\Buyer\IssuedController;
use App\Http\Controllers\Buyer\IndentController;
use App\Http\Controllers\Buyer\ProductWiseStockLedgerController;
use App\Http\Controllers\Buyer\GrnController;
use App\Http\Controllers\Buyer\GetPassController;
use App\Http\Controllers\Buyer\IssueReturnController;
use App\Http\Controllers\Buyer\StockReturnController;
use App\Http\Controllers\Buyer\InventoryPermissionCheckController;
use App\Services\ExportService;
//end inventory section
use App\Http\Controllers\Buyer\SearchProductController;
use App\Http\Controllers\Buyer\ActiveRFQController;
use App\Http\Controllers\Buyer\PiController;
use App\Http\Controllers\Buyer\CISController;
use App\Http\Controllers\Buyer\UserManagementController;
use App\Http\Controllers\Buyer\RolePermissionController;
use App\Http\Controllers\Buyer\RFQUnapprovedOrderController;

Route::name('buyer.')->group(function() {

    Route::middleware(['auth', 'validate_account', 'usertype:1'])->group(function () {
        // common routes
        Route::post('/get-state-by-country-id', [CommonController::class, 'getStateByCountryId'])->name('get-state-by-country-id');
        Route::post('/get-city-by-state-id', [CommonController::class, 'getCityByStateId'])->name('get-city-by-state-id');
        Route::prefix('profile')->group(function() {
            Route::get('/', [BuyerProfileController::class, 'index'])->name('profile');
            Route::post('/validate-buyer-gstin-vat', [BuyerProfileController::class, 'validateBuyerGSTINVat'])->name('validate-buyer-gstin-vat');
            Route::post('/validate-buyer-short-code', [BuyerProfileController::class, 'validateBuyerShortCode'])->name('validate-buyer-short-code');
            Route::post('/save-buyer-profile', [BuyerProfileController::class, 'saveBuyerProfile'])->name('save-buyer-profile');
            Route::get('/profile-complete', [BuyerProfileController::class, 'profileComplete'])->name('profile-complete');
        });

        Route::middleware(['profile_verified'])->group(function () {

            Route::prefix('dashboard')->group(function() {
                Route::get('/', [BuyerDashboardController::class, 'index'])->name('dashboard');
            });
            Route::prefix('setting')->group(function() {
                Route::get('change-password', [BuyerDashboardController::class, 'change_password'])->name('setting.change-password');
            });

            Route::prefix('category')->group(function() {
                Route::get('/category-product/{id}', [CategoryController::class, 'index'])->name('category.product');
                Route::post('/get-category-product', [CategoryController::class, 'getCategoryProduct'])->name('category.get-product');
            });

            Route::prefix('vendor-product')->group(function() {
                Route::get('/{id}', [VendorProductController::class, 'index'])->name('vendor.product');
            });
            Route::prefix('rfq')->group(function() {
                Route::post('draft/add', [RFQDraftController::class, 'addToDraft'])->name('rfq.add-to-draft');
                Route::get('compose/draft-rfq/{draft_id}', [RFQComposeController::class, 'index'])->name('rfq.compose-draft-rfq');
                Route::post('draft-rfq/add', [RFQDraftController::class, 'addToDraftRFQ'])->name('rfq.add-to-draft-rfq');
                Route::get('compose/rfq-success/{rfq_id}', [ComposeRFQController::class, 'composeRFQSuccess'])->name('rfq.compose-rfq-success');


                Route::get('active-rfq', [ActiveRFQController::class, 'index'])->name('rfq.active-rfq');
                Route::get('sent-rfq', [ActiveRFQController::class, 'sent_rfq'])->name('rfq.sent-rfq');
                Route::get('rfq-details/{rfq_id}', [ActiveRFQController::class, 'rfq_details'])->name('rfq.details');
                Route::get('draft-rfq', [RFQDraftController::class, 'index'])->name('rfq.draft-rfq');
                Route::get('pi-invoice', [PiController::class, 'index'])->name('rfq.pi-invoice');
                Route::get('cis-sheet/{rfq_id}', [CISController::class, 'index'])->name('rfq.cis-sheet');
                Route::get('counter-offer/{rfq_id}/{vendor_id}', [CISController::class, 'counter_offer'])->name('rfq.counter-offer');
                Route::get('quotation-received/{rfq_id}/{vendor_id}', [CISController::class, 'quotation_received'])->name('rfq.quotation-received');

            });
            Route::prefix('unapproved-orders')->group(function() {
                Route::get('list', [RFQUnapprovedOrderController::class, 'index'])->name('unapproved-orders.list');
                Route::get('create/{rfq_id}', [RFQUnapprovedOrderController::class, 'create'])->name('unapproved-orders.create');
            });
            Route::prefix('user-management')->group(function () {
                Route::get('users', [UserManagementController::class, 'index'])->name('user-management.users');
                Route::get('add-user', [UserManagementController::class, 'create'])->name('user-management.create-user');
                Route::post('store-user', [UserManagementController::class, 'store'])->name('user-management.store-user');
                Route::get('edit-user/{id}', [UserManagementController::class, 'edit'])->name('user-management.edit-user');
                Route::put('update-user/{id}', [UserManagementController::class, 'update'])->name('user-management.update-user');
            });
            Route::prefix('role-permission')->group(function () {
                Route::get('roles', [RolePermissionController::class, 'index'])->name('role-permission.roles');
                Route::get('add-role', [RolePermissionController::class, 'create'])->name('role-permission.create-role');
                Route::post('store-role', [RolePermissionController::class, 'store'])->name('role-permission.store-role');
                Route::get('edit-role/{id}', [RolePermissionController::class, 'edit'])->name('role-permission.edit-role');
                Route::put('update-role/{id}', [RolePermissionController::class, 'update'])->name('role-permission.update-role');
                Route::delete('delete-role/{id}', [RolePermissionController::class, 'destroy'])->name('role-permission.destroy');
                Route::put('status-role/{id}', [RolePermissionController::class, 'updateStatus'])->name('role-permission.status');
            });

            Route::prefix('ajax')->group(function() {
                Route::post('get-vendor-product', [VendorProductController::class, 'getVendorProduct'])->name('vendor.get-product');

                Route::post('compose/get-draft-product', [RFQComposeController::class, 'getDraftProduct'])->name('rfq.get-draft-product');
                Route::post('compose/search-selected-product', [RFQComposeController::class, 'searchSelectedProduct'])->name('rfq.search-selected-product');
                Route::post('compose/rfq-update-product', [RFQComposeController::class, 'updateProduct'])->name('rfq.update-product');
                Route::post('compose/rfq-update-draft', [RFQComposeController::class, 'updateDraftRFQ'])->name('rfq.update-draft');
                Route::post('compose/rfq-delete-product', [RFQComposeController::class, 'deleteProduct'])->name('rfq.delete-product');
                Route::post('compose/rfq-delete-product-variant', [RFQComposeController::class, 'deleteProductVariant'])->name('rfq.delete-product-variant');
                Route::post('compose/rfq-delete-draft', [RFQComposeController::class, 'deleteDraftRFQ'])->name('rfq.delete-draft');
                Route::post('compose/rfq-compose', [ComposeRFQController::class, 'composeRFQ'])->name('rfq.compose');

                Route::post('search/vendor-product', [SearchProductController::class, 'searchVendorActiveProduct'])->name('search.vendor-product');
                Route::post('search-by-division', [SearchProductController::class, 'getSearchByDivision'])->name('search-by-division');

                Route::post('delete-draft-rfq', [RFQDraftController::class, 'deleteDraftRFQ'])->name('rfq.draft-rfq.delete-draft-rfq');

            });

            // Inventory Section
            Route::prefix('inventory')->group(function() {
                Route::post('/check-permission', [InventoryPermissionCheckController::class, 'check'])->name('inventory.checkPermission');
                // Inventory Routes
                Route::name('inventory.')->group(function () {
                    Route::get('/', [InventoryController::class, 'index'])->name('index');
                    Route::post('/store', [InventoryController::class, 'store'])->name('store');

                    Route::get('/data', [InventoryController::class, 'getData'])->name('data');
                    Route::post('/export', [InventoryController::class, 'exportInventoryData'])->name('exportData');                        
                    Route::get('exportTotal',  [InventoryController::class,'exportTotalInventoryData'])->name('exportTotal');
                    Route::get('exportBatch',  [InventoryController::class,'exportBatchInventoryData'])->name('exportBatch');

                    Route::get('/{id}/edit', [InventoryController::class, 'edit'])
                        ->where('id', '[0-9]+')
                        ->name('edit');

                    Route::delete('/delete', [InventoryController::class, 'deleteInventory'])->name('delete');

                    Route::post('/resetIndentRfq', [InventoryController::class, 'resetInventory'])->name('reset');
                    Route::post('/getInventoryDetailsById', [InventoryController::class, 'getInventoryDetails'])->name('getDetailsByID');

                    Route::post('/fetchInventoryDetailsForAddRfq', [InventoryController::class, 'fetchInventoryDetailsForAddRfq'])->name('fetchInventoryDetailsForAddRfq');
                    Route::post('/generateRFQ', [InventoryController::class, 'generateRFQ'])->name('generateRFQ');
                    Route::get('/activeRfq/{inventoryId}', [InventoryController::class,'getActiveRfqDetails'])->name('activeRfq');
                    Route::get('/orderDetails/{inventoryId}', [InventoryController::class,'getOrderDetails'])->name('orderDetails');
                });

                //get pass modal route
                Route::get('/check-po-pending', [InventoryController::class, 'checkPoPending'])->name('inventory.checkPoPending');
                Route::get('/show-product-name-list', [InventoryController::class, 'showProductNameList'])->name('inventory.showProductNameList');
                Route::get('/getpass-download/{getPassId}', [GetPassController::class, 'downloadPdf']) ->name('getpass.download');

                Route::post('/get-pass/store', [GetPassController::class, 'store'])->name('getpass.store');
                Route::post('/productLifeCycle', [InventoryController::class, 'productLifeCycle'])->name('productLifeCycle');

                // Product Routes
                Route::get('/search-products', [InventoryVendorProductController::class, 'search'])->name('product.search');
                Route::post('/search-allproduct', [InventoryVendorProductController::class, 'searchAllProduct'])->name('search.allproduct');


                // Indent Routes & close indent Routes
                Route::prefix('indent')->name('indent.')->group(function () {
                    Route::get('/', [IndentController::class,'index'])->name('index');
                    Route::get('/indent-list/data', [IndentController::class,'getData'])->name('listdata');
                    Route::post('/store', [IndentController::class,'store'])->name('store');
                    Route::delete('/delete/{id}',[IndentController::class,'destroy'])->name('delete');
                    Route::post('/approve/{id}',[IndentController::class,'approve'])->name('approve');
                    Route::post('/fetch-indent-data', [IndentController::class,'fetchIndentData'])->name('fetchIndentData');
                    Route::post('/getIndentData', [IndentController::class,'getIndentData'])->name('getIndentData');
                    Route::post('/indentexport', [IndentController::class,'exportIndentData'])->name('exportData');
                    Route::post('/approve/{id}', [IndentController::class, 'approve'])->name('approve');
                    Route::post('/bulkApprove', [IndentController::class, 'bulkApprove'])->name('bulkApprove');
                    Route::get('/search-inventory', [IndentController::class, 'searchInventory'])->name('searchInventory');
                    // Route::get('/getMultiIndentData', [IndentController::class, 'getMultiIndentData'])->name('getMultiIndentData');
                    Route::post('/getMultiIndentData', [IndentController::class, 'getMultiIndentData'])->name('getMultiIndentData');


                    // Close Indent
                    Route::get('/close-indent', [IndentController::class,'closeIndent'])->name('closeindent');
                    Route::get('/close-indent-list/data', [IndentController::class,'getDataCloseIndent'])->name('closeindentlistdata');
                    Route::post('/closeindentexport', [IndentController::class,'exportCloseIndentData'])->name('exportDataclose');
                });

                // Bulk Inventory
                Route::prefix('bulk-inventory')->name('bulk.inventory.')->group(function () {
                    Route::get('/import', [BulkInventoryController::class,'importBulkInventory'])->name('import');
                    Route::post('/upload-csv', [BulkInventoryController::class,'uploadCSV'])->name('uploadCSV');
                    Route::post('/delete-row', [BulkInventoryController::class,'deleteRow'])->name('deleteRow');
                    Route::post('/update-row', [BulkInventoryController::class,'updateRowData'])->name('updateRow');
                    Route::post('/check', [BulkInventoryController::class,'checkBulkInventory'])->name('check');
                    Route::post('/update-products', [BulkInventoryController::class,'updateBulkProducts'])->name('updateProducts');
                });

                // Manual PO
                Route::prefix('manualPO')->name('manualPO.')->group(function () {
                    Route::post('/fetchInventory', [ManualPOController::class,'fetchInventoryDetails'])->name('fetchInventory');
                    Route::post('/store', [ManualPOController::class,'store'])->name('store');
                    Route::get('/searchVendorByVendorname', [ManualPOController::class,'searchVendorByVendorname'])->name('search.vendors');
                    Route::get('/getVendorDetailsByName', [ManualPOController::class,'getVendorDetailsByName'])->name('get.vendordetails');
                });
                // Force Closure
                Route::prefix('forceClosure')->name('forceClosure.')->group(function () {
                    Route::post('/fetchInventory', [ForceClosureController::class,'fetchInventoryDetails'])->name('fetchInventory');
                    Route::post('/store', [ForceClosureController::class,'store'])->name('store');
               });
                // WorkOrder
                Route::prefix('workOrder')->name('workOrder.')->group(function () {
                    Route::get('/usercurrency',  [WorkOrderController::class,'userCurrency'])->name('userCurrency');
                    Route::post('/store', [WorkOrderController::class,'store'])->name('store');
                });

                // Issued to
                Route::prefix('issued_to')->name('issued.')->group(function () {
                    Route::get('/getissuedto', [IssuedController::class,'getissuedto'])->name('getissuedto');
                    Route::post('/save-issue-to', [IssuedController::class,'saveIssueTo'])->name('save');
                    Route::post('/delete-issue-to', [IssuedController::class,'deleteIssueTo'])->name('delete');
                });

                //Issue
                Route::prefix('issue')->name('issue.')->group(function () {
                    Route::post('/fetchInventoryDetails', [IssuedController::class,'fetchInventoryDetails'])->name('fetchInventoryDetails');
                    Route::post('/store', [IssuedController::class,'store'])->name('store');
                    Route::post('/consume-store', [IssuedController::class,'ConsumeStore'])->name('consume.store');
                });

                //Issue Return
                Route::prefix('issue_return')->name('issue_return.')->group(function () {
                    Route::post('/fetchInventoryDetails', [IssueReturnController::class,'fetchInventoryDetails'])->name('fetchInventoryDetails');
                    Route::post('/store', [IssueReturnController::class,'store'])->name('store');
                });

                //stock return
                Route::prefix('stock_return')->name('stock_return.')->group(function () {
                    Route::post('/fetchInventoryDetails', [StockReturnController::class,'fetchInventoryDetails'])->name('fetchInventoryDetails');
                    Route::post('/store', [StockReturnController::class,'store'])->name('store');
                });

                // Reports
                Route::prefix('reports')->name('report.')->group(function () {


                    Route::get('indent', [ReportsController::class,'index'])->name('indent');
                    Route::get('closeindent',  [ReportsController::class,'index'])->name('closeindent');
                    Route::get('consume',  [ReportsController::class,'index'])->name('consume');
                    Route::get('issued',  [ReportsController::class,'index'])->name('issued');
                    Route::get('issuereturn',  [ReportsController::class,'index'])->name('issuereturn');
                    Route::get('manualpo',  [ReportsController::class,'index'])->name('manualpo');
                    Route::get('stockLedger',  [ReportsController::class,'index'])->name('stockLedger');
                    Route::get('grn',  [ReportsController::class,'index'])->name('grn');
                    Route::get('getPass',  [ReportsController::class,'index'])->name('getPass');
                    Route::get('pendingGrn',  [ReportsController::class,'index'])->name('pendingGrn');
                    Route::get('currentStock',  [ReportsController::class,'index'])->name('currentStock');
                    Route::get('deadStock',  [ReportsController::class,'index'])->name('deadStock');
                    Route::get('minQty',  [ReportsController::class,'index'])->name('minQty');
                    Route::get('stockReturn',  [ReportsController::class,'index'])->name('stockReturn');
                    Route::get('pendingGrnStockReturn',  [ReportsController::class,'index'])->name('pendingGrnStockReturn');
                    Route::get('workOrder',  [ReportsController::class,'index'])->name('workOrder');



                    // Product Wise Stock Ledger Report
                    Route::prefix('product-wise-stock-ledger')->name('productWiseStockLedger.')->group(function () {
                        Route::get('/fetchlistdata',  [ProductWiseStockLedgerController::class,'fetchData'])->name('listdata');
                        Route::get('/{id}',  [ProductWiseStockLedgerController::class,'index'])->name('index');
                        Route::post('excel', [ProductWiseStockLedgerController::class,'export'])->name('export');
                    });


                    // Get Pass Report
                    Route::prefix('getPass')->group(function () {
                        Route::get('listdata', [GetPassController::class,'gatePassReportlistdata'])->name('getPass.listdata');                    
                        Route::get('exportTotal',  [GetPassController::class,'exportTotalGetPassReport'])->name('getPass.exportTotal');
                        Route::get('exportBatch',  [GetPassController::class,'exportBatchGetPassReport'])->name('getPass.exportBatch');

                    });

                    // Stock ledger Report
                    Route::prefix('stockLedger')->group(function () {
                        Route::get('listdata', [InventoryController::class,'getData'])->name('stockLedger.listdata');
                        Route::post('excel', [InventoryController::class,'exportInventoryData'])->name('stockLedger.export');

                    });
                    // Current Stock Report
                    Route::prefix('currentStock')->group(function () {
                        Route::get('listdata',  [InventoryController::class,'currentStockGetData'])->name('currentStock.listdata');
                        Route::post('excel',  [InventoryController::class,'exportCurrentStockData'])->name('currentStock.export');                        
                        Route::get('exportTotal',  [InventoryController::class,'exportTotalCurrentStockData'])->name('currentStock.exportTotal');
                        Route::get('exportBatch',  [InventoryController::class,'exportBatchCurrentStockData'])->name('currentStock.exportBatch');

                    });
                    // Dead Stock Report
                    Route::prefix('deadStock')->group(function () {
                        Route::get('listdata',  [InventoryController::class,'deadStockGetData'])->name('deadStock.listdata');
                        Route::post('excel',  [InventoryController::class,'exportDeadStockData'])->name('deadStock.export');
                        Route::get('exportTotal',  [InventoryController::class,'exportTotalDeadStockData'])->name('deadStock.exportTotal');
                        Route::get('exportBatch',  [InventoryController::class,'exportBatchDeadStockData'])->name('deadStock.exportBatch');
                    });

                    // Min Qty Report
                    Route::prefix('minQty')->group(function () {
                        Route::get('listdata',  [InventoryController::class,'minQtyGetData'])->name('minQty.listdata');
                        Route::post('excel',  [InventoryController::class,'exportminQtyData'])->name('minQty.export');
                        Route::get('exportTotal',  [InventoryController::class,'exportTotalminQtyData'])->name('minQty.exportTotal');
                        Route::get('exportBatch',  [InventoryController::class,'exportBatchminQtyData'])->name('minQty.exportBatch');
                    });
                    // Manual PO Report
                    Route::prefix('manualpo')->group(function () {
                        Route::get('listdata',  [ManualPOController::class,'listdata'])->name('manualPO.listdata');
                        Route::get('orderDetails/{id}', [ManualPOController::class,'orderDetails'])->name('manualPO.orderDetails');
                        Route::post('excel', [ManualPOController::class,'export'])->name('manualpoReport.export');
                        Route::get('exportTotal',  [ManualPOController::class,'exportTotal'])->name('manualPO.exportTotal');
                        Route::get('exportBatch',  [ManualPOController::class,'exportBatch'])->name('manualPO.exportBatch');
                        Route::post('cancel', [ManualPOController::class,'cancelManualOrder'])->name('manualpo.cancelOrder');
                        Route::get('/download/{id}',[ManualPOController::class,'download'])->name('manualpo.download');
                    });

                    // Issued Report
                    Route::prefix('issue')->group(function () {
                        Route::get('listdata', [IssuedController::class,'getIssuedListData'])->name('issuedlistdata');
                        Route::post('excel', [IssuedController::class,'export'])->name('issuedExport');
                        Route::get('exportTotal',  [IssuedController::class,'exportTotal'])->name('exportTotalIssued');
                        Route::get('exportBatch',  [IssuedController::class,'exportBatch'])->name('exportBatchIssued');
                    });

                    // Consume Report
                    Route::prefix('consume')->group(function () {
                        Route::get('listdata', [IssuedController::class,'getConsumeListData'])->name('consume.listdata');
                        Route::get('exportTotal',  [IssuedController::class,'exportTotalConsume'])->name('consume.exportTotalConsume');
                        Route::get('exportBatch',  [IssuedController::class,'exportBatchConsume'])->name('consume.exportBatchConsume');
                    });

                    // Issued Return Report
                    Route::prefix('issueReturn')->group(function () {
                        Route::get('listdata', [IssueReturnController::class,'getIssueReturnListData'])->name('issuedReturnlistdata');
                        Route::post('excel', [IssueReturnController::class,'export'])->name('issuedReturnExport');
                        Route::get('exportTotal', [IssueReturnController::class,'exportTotal'])->name('exportTotalIssueReturn');
                        Route::get('exportBatch', [IssueReturnController::class,'exportBatch'])->name('exportBatchIssueReturn');
                    });

                    // Indent Reports
                    Route::get('indent/listdata', [IndentController::class,'getindentreportData'])->name('indentlistdata');
                    Route::post('excel', [IndentController::class,'exportIndentreportData'])->name('exportIndentReport');
                    Route::get('/activeRfq/{indentId}', [IndentController::class,'getActiveRfqDetails'])->name('activeIndentRfq');
                    Route::post('/fetchInventoryDetailsForAddRfq', [IndentController::class, 'fetchInventoryDetailsForAddRfq'])->name('fetchInventoryDetailsForAddRfq');
                    Route::get('indent/exportTotal',  [IndentController::class,'exportTotalIndentreportData'])->name('exportTotalIndentreportData');
                    Route::get('indent/exportBatch',  [IndentController::class,'exportBatchIndentreportData'])->name('exportBatchIndentreportData');

                    

                    Route::get('closeindent/listdata', [IndentController::class,'getcloseindentreportData'])->name('closeindentlistdata');
                    Route::post('closeindent/export', [IndentController::class,'exportCloseIndentData'])->name('exportcloseIndentReport');
                    Route::post('closeindent/data', [IndentController::class,'closeindentdata'])->name('closeindentdata');
                    Route::get('closeindent/exportTotal',  [IndentController::class,'exportTotalcloseindentdata'])->name('exportTotalcloseindentdata');
                    Route::get('closeindent/exportBatch',  [IndentController::class,'exportBatchcloseindentdata'])->name('exportBatchcloseindentdata');


                    // grn Reports
                    Route::get('grn/listdata', [GrnController::class,'grnReportlistdata'])->name('grnReportlistdata');
                    Route::post('grn/export', [GrnController::class,'exportGrnReport'])->name('exportGrnReport');
                    Route::get('grn/exportTotal',  [GrnController::class,'exportTotalGrnReport'])->name('exportTotalGrnReport');
                    Route::get('grn/exportBatch',  [GrnController::class,'exportBatchGrnReport'])->name('exportBatchGrnReport');
                    Route::post('grn/data', [GrnController::class,'fetchGrnRowdata'])->name('fetchGrnRowdata');
                    Route::post('grn/updatedata', [GrnController::class,'editGrnRowdata'])->name('editGrnRowdata');
                    Route::get('grn/downloaddata/{id}', [GrnController::class,'downloadGrnRowdata'])->name('downloadGrnRowdata');

                    //prndinggrn reports
                    Route::get('pendingGrn/listdata', [GrnController::class,'pendingGrnReportlistdata'])->name('pendingGrnReportlistdata');
                    Route::post('pendingGrn/export', [GrnController::class,'exportPendingGrnReport'])->name('exportPendingGrnReport');
                    Route::get('pendingGrn/exportTotal',  [GrnController::class,'exportTotalPendingGrnReport'])->name('exportTotalPendingGrnReport');
                    Route::get('pendingGrn/exportBatch',  [GrnController::class,'exportBatchPendingGrnReport'])->name('exportBatchPendingGrnReport');
                    Route::post('pendingGrn/fetchOrderDetails', [GrnController::class, 'fetchOrderDetailsforPendingGrn'])->name('fetchOrderDetailsforPendingGrn');
                    Route::post('pendingGrn/store', [GrnController::class,'storeFromPendingGRN'])->name('storeFromPendingGRN');
                    
                    //prndinggrnstockreturn reports
                    Route::get('pendingGrnStockReturn/listdata', [GrnController::class,'pendingGrnStockReturnReportlistdata'])->name('pendingGrnStockReturnReportlistdata');
                    Route::post('pendingGrnStockReturn/export', [GrnController::class,'exportPendingGrnStockReturnReport'])->name('exportPendingGrnStockReturnReport');
                    Route::get('pendingGrnStockReturn/exportTotal', [GrnController::class,'exportTotalPendingGrnStockReturnReport'])->name('exportTotalPendingGrnStockReturnReport');
                    Route::get('pendingGrnStockReturn/exportBatch', [GrnController::class,'exportBatchPendingGrnStockReturnReport'])->name('exportBatchPendingGrnStockReturnReport');



                    //stock return report
                    Route::get('stockReturn/listdata', [StockReturnController::class,'stockReturnReportlistdata'])->name('stockReturnReportlistdata');
                    Route::post('stockReturn/export', [StockReturnController::class,'exportStockReturnReport'])->name('exportStockReturnReport');                    
                    Route::get('stockReturn/exportTotal', [StockReturnController::class,'exportTotal'])->name('exportTotalStockReturn');
                    Route::get('stockReturn/exportBatch', [StockReturnController::class,'exportBatch'])->name('exportBatchStockReturn');
                    Route::post('stockReturn/data', [StockReturnController::class,'fetchStockReturnRowdata'])->name('fetchStockReturnRowdata');
                    Route::post('stockReturn/updatedata', [StockReturnController::class,'editStockReturnRowdata'])->name('editStockReturnRowdata');

                    // Work Order Report
                    Route::prefix('workOrder')->group(function () {
                        Route::get('listdata',  [WorkOrderController::class,'listdata'])->name('workOrder.listdata');
                        Route::get('exportTotal',  [WorkOrderController::class,'exportTotal'])->name('workOrder.exportTotal');
                        Route::get('exportBatch',  [WorkOrderController::class,'exportBatch'])->name('workOrder.exportBatch');
                        Route::get('/download/{id}',[WorkOrderController::class,'download'])->name('workOrder.download');
                    });
                });

                //Add grn
                Route::prefix('grn')->name('grn.')->group(function () {
                    Route::get('/check-grn-entry/{inventoryId}', [GrnController::class,'checkGrnEntry'])->name('checkGrnEntry');
                    Route::post('/store', [GrnController::class,'store'])->name('store');
                });

                //store excel file delete
                Route::post('/delete-export-file', function (Request $request, ExportService $exportService) {
                    return response()->json(
                        $exportService->deleteExportFile($request->input('file_path'))
                    );
                })->name('delete.export.file');

                Route::get('/download-and-delete', function(Request $request, ExportService $service) {
                    return $service->downloadAndDeleteFile($request->query('path'));
                })->name('downloadAndDelete');//pingki

            });
            Route::get('/order-confirmed', function () {
                return 'Order Confirmed Route Working! (test message)';
            })->name('rfq.order-confirmed.view');

        });
    });
});
