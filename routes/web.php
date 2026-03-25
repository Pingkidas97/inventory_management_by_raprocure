<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VerifiedProductController;
use App\Http\Controllers\Admin\ExportVerifiedProductController;
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\EditProductRequestController;
use App\Http\Controllers\Admin\NewProductRequestController;
use App\Http\Controllers\Admin\BulkApprovalProductsController;
use App\Http\Controllers\Admin\VendorProductController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\HelpSupportController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\BuyerController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\CommonController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ProductsCategoryDivisionReportController;
use App\Http\Controllers\Admin\BuyerActivityReportController;
use App\Http\Controllers\Admin\VendorDisabledProductReportController;
use App\Http\Controllers\Admin\RFQSummaryReportController;
use App\Http\Controllers\Admin\VendorActivityReportController;
use App\Http\Controllers\Admin\AuctionRFQSummaryReportController;
use App\Http\Controllers\Admin\ManualPOReportController;//pingki


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/clear-cache', function () {
    
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    return '<h1>Cache Cleared</h1>';
});
Route::get('/test-mail', [HomeController::class, 'index']);
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot-password');
Route::post('/forgot-password', [LoginController::class, 'forgotPasswordSubmit'])->name('forgot-password.submit');
Route::get('/reset-password/{token}', [LoginController::class, 'resetPassword'])->name('reset-password');
Route::post('/reset-password', [LoginController::class, 'resetPasswordSubmit'])->name('reset-password.submit');

//////////////////////////////////start super admin manual po/////////////////////////////////pingki

Route::get('/smpo', [ManualPOReportController::class, 'index'])->name('admin.reports.manualPO');
Route::post('/manualpoReport/excel', [ManualPOReportController::class,'export'])->name('admin.reports.manualpoReport.export');
Route::get('manualpoReport/download-and-delete/{path}', [ManualPOReportController::class, 'downloadAndDeleteFile'])
    ->name('admin.reports.manualpoReport.downloadAndDelete');
///////////////////////////////////end super admin manual po//////////////////////////////////////

Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
Route::get('/verification-code', [RegisterController::class, 'verificationCode'])->name('register.verification-code');
Route::post('/verify-verification-code', [RegisterController::class, 'verifyVerificationCode'])->name('register.verify-verification-code');
Route::post('/resend-verification-code', [RegisterController::class, 'resendVerificationCode'])->name('register.resend-verification-code');

Route::group(['prefix' => 'super-admin'], function() {
    Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
    
    Route::get('/forgot-password', [AdminLoginController::class, 'forgotPassword'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminLoginController::class, 'forgotPasswordSubmit'])->name('admin.forgot-password.submit');
    Route::get('/reset-password/{token}', [AdminLoginController::class, 'resetPassword'])->name('admin.reset-password');
    Route::post('/reset-password', [AdminLoginController::class, 'resetPasswordSubmit'])->name('admin.reset-password.submit');

    
    Route::middleware(['auth','usertype:3'])->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.admin-dashboard');
        })->name('admin.dashboard');
        Route::get('/change-password',[UserController::class, 'changePassword'] )->name('admin.password.change');
        Route::post('/update-password',[UserController::class, 'updatePassword'] )->name('admin.password.update');

        // common routes
        Route::prefix('vendor')->name('admin.')->group(function() {
            Route::post('/get-state-by-country-id', [CommonController::class, 'getStateByCountryId'])->name('get-state-by-country-id');
            Route::post('/get-city-by-state-id', [CommonController::class, 'getCityByStateId'])->name('get-city-by-state-id');
        });

        // User Roles
        Route::prefix('user-roles')->name('admin.')->group(function () {
            Route::get('/', [UserRoleController::class, 'index'])->name('user-roles.index');
            Route::get('/create', [UserRoleController::class, 'create'])->name('user-roles.create');
            Route::get('/{id}/edit', [UserRoleController::class, 'edit'])->name('user-roles.edit');
            Route::post('/', [UserRoleController::class, 'store'])->name('user-roles.store');
            Route::put('/{id}', [UserRoleController::class, 'update'])->name('user-roles.update');
            Route::delete('/{id}', [UserRoleController::class, 'destroy'])->name('user-roles.destroy');
            Route::put('/{id}/status', [UserRoleController::class, 'updateStatus'])->name('user-roles.status');
        });
        // Users
        Route::prefix('users')->name('admin.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::put('/{id}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');
            Route::get('users-export', [UserController::class, 'export'])->name('users.export');
        });
        // Divisions
        Route::prefix('divisions')->name('admin.')->group(function () {
            Route::get('/', [DivisionController::class, 'index'])->name('divisions.index');
            Route::get('/create', [DivisionController::class, 'create'])->name('divisions.create');
            Route::get('/{id}/edit', [DivisionController::class, 'edit'])->name('divisions.edit');
            Route::post('/', [DivisionController::class, 'store'])->name('divisions.store');
            Route::put('/{id}', [DivisionController::class, 'update'])->name('divisions.update');
            Route::delete('/{id}', [DivisionController::class, 'destroy'])->name('divisions.destroy');
            Route::put('/{id}/status', [DivisionController::class, 'updateStatus'])->name('divisions.updateStatus');
        });
        // Categories
        Route::prefix('categories')->name('admin.')->group(function () {
            Route::get('/{id?}', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/create/{id?}', [CategoryController::class, 'create'])->name('categories.create');
            Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('/{id}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
            Route::put('/{id}/status', [CategoryController::class, 'updateStatus'])->name('categories.updateStatus');
        });
        // Products
        Route::prefix('products')->name('admin.')->group(function () {
            Route::get('/{id?}', [ProductController::class, 'index'])->name('products.index');
            Route::get('/create/{id?}', [ProductController::class, 'create'])->name('products.create');
            Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::post('/', [ProductController::class, 'store'])->name('products.store');
            Route::put('/{id}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::put('/{id}/status', [ProductController::class, 'updateStatus'])->name('products.updateStatus');
        });
        Route::get('admin/getCategoriesByDivision', [CategoryController::class, 'getCategoriesByDivision'])->name('admin.getCategoriesByDivision');
        // All Verified Products
        Route::prefix('verified-products')->name('admin.')->group(function () {
            Route::get('/', [VerifiedProductController::class, 'index'])->name('verified-products.index');
            Route::put('/{id}/status', [VerifiedProductController::class, 'updateStatus'])->name('verified-products.updateStatus');
            Route::get('/{id}/view', [VerifiedProductController::class, 'show'])->name('verified-products.view');
            Route::get('/{id}/edit', [VerifiedProductController::class, 'edit'])->name('verified-products.edit');
            Route::put('/{id}', [VerifiedProductController::class, 'update'])->name('verified-products.update');
            Route::delete('/{id}', [VerifiedProductController::class, 'destroy'])->name('verified-products.destroy');
            
            Route::get('/export/total', [VerifiedProductController::class, 'exportTotal'])->name('verified-products.exportTotal');
            Route::get('/export/batch', [VerifiedProductController::class, 'exportBatch'])->name('verified-products.exportBatch');
            

            Route::post('/export', [VerifiedProductController::class, 'export'])->name('verified-products.export');
            Route::get('/batch-progress/{id}', [VerifiedProductController::class, 'batchProgress'])->name('buverified-productser.batch.progress');
            Route::get('/export/download/{id}', [VerifiedProductController::class, 'downloadExport'])->name('verified-products.export.download');
        

            //Route::get('export', [VerifiedProductController::class, 'export'])->name('verified-products.export');
            Route::get('export', [VerifiedProductController::class, 'export'])->name('verified-products.export');
            Route::post('/update-tags', [VerifiedProductController::class, 'updateTags'])->name('verified-products.update-tags');
        });
         // Export routes
        Route::prefix('verified-products-exports')->name('admin.')->group(function () {
            // Main export interface
            Route::get('/', [ExportVerifiedProductController::class, 'index'])->name('exports.index');
            // Verified products export
            Route::post('/verified-products', [ExportVerifiedProductController::class, 'exportVerifiedProducts'])
                ->name('exports-verified-products');
            // Export status check
            Route::get('/status/{exportId}', [ExportVerifiedProductController::class, 'checkStatus'])
                ->name('exports.status');
            // Download export
            Route::get('/download/{exportId}', [ExportVerifiedProductController::class, 'download'])
                ->name('exports.download');
        });

        // All Products for Approval
        Route::prefix('product-approvals')->name('admin.')->group(function () {
            Route::get('/', [ProductApprovalController::class, 'index'])->name('product-approvals.index');
            Route::get('/{id}/approval', [ProductApprovalController::class, 'approval'])->name('product-approvals.approval');
            Route::put('/{id}', [ProductApprovalController::class, 'update'])->name('product-approvals.update');
            
        });
        // All Edit Product 
        Route::prefix('edit-product-requests')->name('admin.')->group(function () {
            Route::get('/', [EditProductRequestController::class, 'index'])->name('edit-products.index');
            Route::get('/{id}/approval', [EditProductRequestController::class, 'approval'])->name('edit-products.approval');
            Route::put('/{id}', [EditProductRequestController::class, 'update'])->name('edit-products.update');
        });
       
        Route::prefix('new-product-requests')->name('admin.')->group(function () {
            Route::get('/', [NewProductRequestController::class, 'index'])->name('new-products.index');
            Route::get('/{id}/approval', [NewProductRequestController::class, 'approval'])->name('new-products.approval');
            Route::put('/{id}', [NewProductRequestController::class, 'update'])->name('new-products.update');
            // Add the delete route here
            Route::delete('/{id}', [NewProductRequestController::class, 'destroy'])->name('new-products.delete');
        });

        Route::get('/product-autocomplete', [NewProductRequestController::class, 'autocomplete'])
        ->name('admin.product.autocomplete');

        Route::prefix('bulk-product-requests')->name('admin.')->group(function () {
            Route::get('/', [BulkApprovalProductsController::class, 'index'])->name('bulk-products.index');
            Route::get('/{id}/approval/{group_id}', [BulkApprovalProductsController::class, 'approval'])->name('bulk-products.approval');
            Route::put('/{id}', [BulkApprovalProductsController::class, 'update'])->name('bulk-products.update');
            // Add the delete route here
            Route::delete('/{id}', [BulkApprovalProductsController::class, 'destroy'])->name('bulk-products.delete');
            Route::post('/delete-multiple', [BulkApprovalProductsController::class, 'deleteMultiple'])->name('bulk-products.delete-multiple');
            Route::post('/update-multiple', [BulkApprovalProductsController::class, 'updateMultiple'])->name('bulk-products.update-multiple');

        });
        
        Route::prefix('vendor-products')->name('admin.')->group(function () {
           Route::get('/create/{id?}', [VendorProductController::class, 'create'])->name('vendor.products.create');
           Route::post('store', [VendorProductController::class, 'store'])->name('vendor.products.store');
           Route::get('/bulk/create/{id?}', [VendorProductController::class, 'bulk_create'])->name('vendor.products.bulk_create');

         
           Route::post('/bulkstore', [VendorProductController::class, 'bulkstore'])->name('vendor.products.bulkstore');
          

           Route::post('vendor/products/get-products-by-category', [VendorProductController::class, 'get_products_by_category'])->name('vendor.products.get_products_by_category');

        });

        // Advertisement
        Route::prefix('advertisement')->name('admin.')->group(function () {
            Route::get('/', [AdvertisementController::class, 'index'])->name('advertisement.index');
            Route::get('/create', [AdvertisementController::class, 'create'])->name('advertisement.create');
            Route::post('/store', [AdvertisementController::class, 'store'])->name('advertisement.store');
            Route::get('/edit/{id}', [AdvertisementController::class, 'edit'])->name('advertisement.edit');
            Route::put('/update/{id}', [AdvertisementController::class, 'update'])->name('advertisement.update');
            Route::delete('/destroy/{id}', [AdvertisementController::class, 'destroy'])->name('advertisement.destroy');
            Route::post('/list', [AdvertisementController::class, 'list'])->name('advertisement.list');
        });

        // Plan
        Route::prefix('plan')->name('admin.')->group(function() {
            Route::get('/', [PlanController::class, 'index'])->name('plan.index');
            Route::get('/create', [PlanController::class, 'create'])->name('plan.create');
            Route::post('/store', [PlanController::class, 'store'])->name('plan.store');
            Route::get('/edit/{id}', [PlanController::class, 'edit'])->name('plan.edit');
            Route::put('/update/{id}', [PlanController::class, 'update'])->name('plan.update');
            Route::post('/destroy/{id}', [PlanController::class, 'destroy'])->name('plan.destroy');
            Route::post('/list', [PlanController::class, 'list'])->name('plan.list');
        });

        // Help Support 
        Route::prefix('help-support')->name('admin.')->group(function() {
            Route::get('/', [HelpSupportController::class, 'index'])->name('help_support.index');
            Route::get('/create', [HelpSupportController::class, 'create'])->name('help_support.create');
            Route::post('/store', [HelpSupportController::class, 'store'])->name('help_support.store');
            Route::get('/edit/{id}', [HelpSupportController::class, 'edit'])->name('help_support.edit');
            Route::put('/update/{id}', [HelpSupportController::class, 'update'])->name('help_support.update');
            Route::post('/destroy/{id}', [HelpSupportController::class, 'destroy'])->name('help_support.destroy');
            Route::post('/list', [HelpSupportController::class, 'list'])->name('help_support.list');
        }); 

        // Notification
        Route::prefix('notification')->name('admin.')->group(function() {
            Route::get('/', [NotificationController::class, 'index'])->name('notification.index');
            Route::post('/list', [NotificationController::class, 'list'])->name('notification.list');
        });

        // Buyer
        Route::prefix('buyer')->name('admin.')->group(function() {
            Route::get('/', [BuyerController::class, 'index'])->name('buyer.index');
            Route::get('/profile/{id}', [BuyerController::class, 'profile'])->name('buyer.profile');
            Route::get('/plan/{id}', [BuyerController::class, 'plan'])->name('buyer.plan');
            Route::get('/user/{id}', [BuyerController::class, 'users'])->name('buyer.user');
            Route::put('/plan/{id}', [BuyerController::class, 'planUpdate'])->name('buyer.plan.update');
            Route::post('/profile/status', [BuyerController::class, 'profileStatus'])->name('buyer.profile.status');
            Route::post('/status', [BuyerController::class, 'status'])->name('buyer.status');
            Route::post('/delete', [BuyerController::class, 'delete'])->name('buyer.delete');
            Route::post('/inventory/status', [BuyerController::class, 'inventoryStatus'])->name('buyer.inventory.status');
            Route::post('/api/status', [BuyerController::class, 'apiStatus'])->name('buyer.api.status');
            Route::post('/currency', [BuyerController::class, 'currency'])->name('buyer.currency');
            Route::post('/profile/update', [BuyerController::class, 'updateProfile'])->name('buyer.profile.update');

            Route::get('/export/total', [BuyerController::class, 'exportTotalBuyer'])->name('buyer.exportBuyerTotal');
            Route::get('/export/batch', [BuyerController::class, 'exportBatchBuyer'])->name('buyer.exportBuyerBatch');
            
            Route::get('/export/user/total', [BuyerController::class, 'exportTotalUser'])->name('buyer.exportUserTotal');
            Route::get('/export/user/batch', [BuyerController::class, 'exportBatchUser'])->name('buyer.exportUserBatch');
            
            Route::post('/export-buyer', [BuyerController::class, 'exportBuyer'])->name('buyer.export');
            //Route::get('/batch-progress/{id}', [BuyerController::class, 'buyerBatchProgress'])->name('buyer.batch.progress');
            //Route::get('/export/download/{id}', [BuyerController::class, 'downloadBuyerExport'])->name('buyer.export.download');
        });

        // Vendor
        Route::prefix('vendor')->name('admin.')->group(function() {
            Route::get('/', [VendorController::class, 'index'])->name('vendor.index');
            Route::get('/registration', [VendorController::class, 'registration'])->name('vendor.registration');
            Route::post('/sa-registration-vendor', [VendorController::class, 'saVendorRegistration'])->name('vendor.sa-vendor-registration');
            Route::get('/sa-vendor-profile/{user_id}', [VendorController::class, 'vendorProfileBySA'])->name('vendor.sa-vendor-profile');
            Route::post('/validate-vendor-gstin-vat', [VendorController::class, 'validateVendorGSTINVat'])->name('vendor.validate-vendor-gstin-vat');
            Route::post('/save-sa-vendor-profile', [VendorController::class, 'saveSAVendorProfile'])->name('vendor.save-sa-vendor-profile');
            Route::get('/profile/{id}', [VendorController::class, 'profile'])->name('vendor.profile');
            Route::get('/plan/{id}', [VendorController::class, 'plan'])->name('vendor.plan');
            Route::get('/user/{id}', [VendorController::class, 'users'])->name('vendor.user');
            Route::put('/plan/{id}', [VendorController::class, 'planUpdate'])->name('vendor.plan.update');
            Route::post('/profile/status', [VendorController::class, 'profileStatus'])->name('vendor.profile.status');
            Route::post('/status', [VendorController::class, 'status'])->name('vendor.status');
            Route::post('/delete', [VendorController::class, 'delete'])->name('vendor.delete');
            Route::post('/inventory/status', [VendorController::class, 'inventoryStatus'])->name('vendor.inventory.status');
            Route::post('/api/status', [VendorController::class, 'apiStatus'])->name('vendor.api.status');
            Route::post('/currency', [VendorController::class, 'currency'])->name('vendor.currency');
            Route::post('/profile/update', [VendorController::class, 'updateProfile'])->name('vendor.profile.update');

            Route::get('/export/total', [VendorController::class, 'exportTotal'])->name('vendor.exportTotal');
            Route::get('/export/batch', [VendorController::class, 'exportBatch'])->name('vendor.exportBatch'); 
        });
        
        //Accounts Module
        Route::prefix('accounts')->name('admin.')->group(function() {
            Route::get('/buyer', [AccountController::class, 'buyer'])->name('accounts.buyer');
            Route::post('/buyer/manager', [AccountController::class, 'buyerManager'])->name('accounts.buyer.manager');
            Route::post('/buyer/plan/extend', [AccountController::class, 'buyerPlanExtend'])->name('accounts.buyer.plan.extend');
            Route::get('/buyer/plan/view/{id}', [AccountController::class, 'buyerPlanView'])->name('accounts.buyer.plan.view');
            Route::get('/buyer/plan/invoice/{id}', [AccountController::class, 'buyerPlanInvoice'])->name('accounts.buyer.plan.invoice');
            Route::get('/buyer/export/total', [AccountController::class, 'exportBuyerTotal'])->name('accounts.exportBuyerTotal');
            Route::get('/buyer/export/batch', [AccountController::class, 'exportBuyerBatch'])->name('accounts.exportBuyerBatch');
           
           
           
           
            Route::get('/vendor', [AccountController::class, 'vendor'])->name('accounts.vendor');
            Route::post('/vendor/manager', [AccountController::class, 'vendorManager'])->name('accounts.vendor.manager');
            Route::post('/vendor/plan/extend', [AccountController::class, 'vendorPlanExtend'])->name('accounts.vendor.plan.extend');
            Route::get('/vendor/plan/view/{id}', [AccountController::class, 'vendorPlanView'])->name('accounts.vendor.plan.view');
            Route::post('/update-free-plan-for-all-vendors', [AccountController::class, 'updateFreePlanForAllVendors'])->name('accounts.vendor.plan.extend.bulk');
            Route::get('/vendor/plan/invoice/{id}', [AccountController::class, 'vendorPlanInvoice'])->name('accounts.vendor.plan.invoice');
            Route::get('/vendor/export/total', [AccountController::class, 'exportVendorTotal'])->name('accounts.exportVendorTotal');
            Route::get('/vendor/export/batch', [AccountController::class, 'exportVendorBatch'])->name('accounts.exportVendorBatch');
        });

        Route::prefix('reports')->name('admin.')->group(function() {
           
            Route::get('product-division-category', [ProductsCategoryDivisionReportController::class, 'index'])->name('reports.product-division-category');
            Route::get('product-division-category/export/total', [ProductsCategoryDivisionReportController::class, 'exportTotal'])->name('product-division-category.exportTotal');
            Route::get('product-division-category/export/batch', [ProductsCategoryDivisionReportController::class, 'exportBatch'])->name('product-division-category.exportBatch');
            
            Route::get('buyer-activity', [BuyerActivityReportController::class, 'index'])->name('reports.buyer-activity');
            Route::get('buyer-activity/export/total', [BuyerActivityReportController::class, 'exportTotal'])->name('buyer-activity.exportTotal');
            Route::get('buyer-activity/export/batch', [BuyerActivityReportController::class, 'exportBatch'])->name('buyer-activity.exportBatch');
            
            Route::get('auction-rfqs-summary',[AuctionRFQSummaryReportController::class, 'index'])->name('reports.auction-rfqs-summary');
            Route::get('auction-rfqs-summary/export/total', [AuctionRFQSummaryReportController::class, 'exportTotal'])->name('auction-rfqs-summary.exportTotal');
            Route::get('auction-rfqs-summary/export/batch', [AuctionRFQSummaryReportController::class, 'exportBatch'])->name('auction-rfqs-summary.exportBatch');
        
        });
        Route::prefix('vendor-disabled-product-report')->name('admin.')->group(function () {
            Route::get('/', [VendorDisabledProductReportController::class, 'index'])->name('vendor-disabled-product-report.index');
            Route::post('/bulk-delete', [VendorDisabledProductReportController::class, 'bulkDelete']) ->name('vendor-disabled-product-report.bulkDelete');
            Route::get('/export-total', [VendorDisabledProductReportController::class, 'exportTotal'])->name('vendor-disabled-product-report.exportTotal');
            Route::get('/export-batch', [VendorDisabledProductReportController::class, 'exportBatch'])->name('vendor-disabled-product-report.exportBatch');

        });

        Route::prefix('rfq-summary-report')->name('admin.')->group(function () {
            Route::get('/', [RFQSummaryReportController::class, 'index'])
                ->name('rfq-summary-report.index');
            Route::get('/exportTotal', [RFQSummaryReportController::class, 'exportTotal'])->name('rfq-summary-report.exportTotal');
            Route::get('/exportBatch', [RFQSummaryReportController::class, 'exportBatch'])->name('rfq-summary-report.exportBatch');

        });

         Route::prefix('vendor-activity-report')->name('admin.')->group(function () {
            Route::get('/', [VendorActivityReportController::class, 'index'])
                ->name('vendor-activity-report.index');
        });

        



    });
});




// For Vendor Dashboard
Route::group(['prefix' => 'vendor'], function() {
    require __DIR__ . '/vendor.php';
});
// For Buyer Dashboard
Route::group(['prefix' => 'buyer'], function() {
    require __DIR__ . '/buyer.php';
});



Route::get('/refresh-csrf', function () {
    return response()->json(['csrf' => csrf_token()]);
})->name('web.csrf');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('user.logout');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');


Route::get('403',function () {
        return view('errors.403');
    }
)->name('403.unauthorized');