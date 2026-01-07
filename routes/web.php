<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
Route::get('/updateDriverStatus/{id}', [App\Http\Controllers\HomeController::class, 'updateDriverStatus'])->name('updatestatus');
Route::get('home/sales_overview', [App\Http\Controllers\HomeController::class, 'getSalesOverview']);
Route::get('lang/change', [App\Http\Controllers\LanguageController::class, 'change'])->name('changeLang');
Route::get('/getlang', [App\Http\Controllers\LanguageController::class, 'getLangauage'])->name('language.header');
Route::post('/gecode/{slugid}', [App\Http\Controllers\LanguageController::class, 'getCode'])->name('lang.code');

Route::post('payments/getpaytmchecksum', [App\Http\Controllers\PaymentController::class, 'getPaytmChecksum']);
Route::post('payments/validatechecksum', [App\Http\Controllers\PaymentController::class, 'validateChecksum']);
Route::post('payments/initiatepaytmpayment', [App\Http\Controllers\PaymentController::class, 'initiatePaytmPayment']);
Route::get('payments/paytmpaymentcallback', [App\Http\Controllers\PaymentController::class, 'paytmPaymentcallback']);
Route::post('payments/paypalclientid', [App\Http\Controllers\PaymentController::class, 'getPaypalClienttoken']);
Route::post('payments/paypaltransaction', [App\Http\Controllers\PaymentController::class, 'createBraintreePayment']);
Route::post('payments/stripepaymentintent', [App\Http\Controllers\PaymentController::class, 'createStripePaymentIntent']);
Route::post('payments/razorpay/createorder', [App\Http\Controllers\RazorPayController::class, 'createOrderid']);

Route::group(['middleware' => ['permission:language.create, permission:language.delete, permission:language.edit, permission:language.index']], function () {
    Route::get('language', [App\Http\Controllers\LanguageController::class, 'index'])->name('language.index');
    Route::get('/language/create', [App\Http\Controllers\LanguageController::class, 'create'])->name('language.create');
    Route::post('/language/storeuser', [App\Http\Controllers\LanguageController::class, 'storeuser'])->name('language.storeuser');
    Route::get('/language/edit/{id}', [App\Http\Controllers\LanguageController::class, 'edit'])->name('language.edit');
    Route::put('language/update/{id}', [App\Http\Controllers\LanguageController::class, 'userUpdate'])->name('language.update');
    Route::get('/language/delete/{id}', [App\Http\Controllers\LanguageController::class, 'deleteUser'])->name('language.delete');
    Route::post('language/switch', [App\Http\Controllers\LanguageController::class, 'toggalSwitch'])->name('language.switch');
});

Route::group(['middleware' => ['permission:users.create, permission:users.delete, permission:users.edit, permission:users.index, permission:users.show']], function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/edit/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::get('/user/delete/{id}', [App\Http\Controllers\UserController::class, 'deleteUser'])->name('users.delete');
    Route::put('user/update/{id}', [App\Http\Controllers\UserController::class, 'userUpdate'])->name('users.update');
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/users/storeuser', [App\Http\Controllers\UserController::class, 'storeuser'])->name('users.storeuser');
    Route::get('/users/show/{id}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::post('/users/add-wallet/{id}', [App\Http\Controllers\UserController::class, 'addWallet'])->name('users.wallet');
    Route::get('/users/changeStatus/{id}', [App\Http\Controllers\UserController::class, 'changeStatus'])->name('users.changeStatus');
    Route::post('/switch', [App\Http\Controllers\UserController::class, 'toggalSwitch']);
});

Route::get('/users/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('users.profile');
Route::post('/users/profile/update/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('users.profile.update');

Route::group(['middleware' => ['permission:driver.delete, permission:driver.show, permission:drivers.create,permission:drivers.edit,permission:drivers.index']], function () {
    Route::get('/drivers', [App\Http\Controllers\DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/approved', [App\Http\Controllers\DriverController::class, 'index'])->name('drivers.approved');
    Route::get('/drivers/pending', [App\Http\Controllers\DriverController::class, 'index'])->name('drivers.pending');
    Route::get('/drivers/edit/{id}', [App\Http\Controllers\DriverController::class, 'edit'])->name('drivers.edit');
    Route::get('/drivers/create', [App\Http\Controllers\DriverController::class, 'create'])->name('drivers.create');
    Route::post('/drivers/store', [App\Http\Controllers\DriverController::class, 'store'])->name('drivers.store');
    Route::get('/driver/delete/{id}', [App\Http\Controllers\DriverController::class, 'deleteDriver'])->name('driver.delete');
    Route::put('driver/update/{id}', [App\Http\Controllers\DriverController::class, 'updateDriver'])->name('driver.update');
    Route::get('/driver/show/{id}', [App\Http\Controllers\DriverController::class, 'show'])->name('driver.show');
    Route::post('/driver/add-wallet/{id}', [App\Http\Controllers\DriverController::class, 'addWallet'])->name('driver.wallet');
    Route::get('/driver/changeStatus/{id}', [App\Http\Controllers\DriverController::class, 'changeStatus'])->name('driver.changeStatus');
    Route::get('/driver/changeStatus/{id}', [App\Http\Controllers\DriverController::class, 'changeStatus'])->name('driver.changeStatus');
    Route::get('/document/view/{id}', [App\Http\Controllers\DriverController::class, 'documentView'])->name('driver.documentView');
    Route::get('/uploaddocument/{id}/{doc_id}', [App\Http\Controllers\DriverController::class, 'uploaddocument'])->name('driver.uploaddocument');
    Route::put('/driver/updatedocument/{id}', [App\Http\Controllers\DriverController::class, 'updatedocument'])->name('driver.updatedocument');
    Route::get('/documentstatus/{id}/{type}', [App\Http\Controllers\DriverController::class, 'statusAproval'])->name('drivers.documentstatus');
    Route::post('/driver/model/{brandId}', [App\Http\Controllers\DriverController::class, 'getModel'])->name('driver.model');
    Route::post('/driver/brand/{vehicleType_id}', [App\Http\Controllers\DriverController::class, 'getBrand'])->name('driver.brand');
    Route::get('/driver/download', [App\Http\Controllers\DriverController::class, 'download'])->name('driver.download');
    Route::get('status-update/{id}', [App\Http\Controllers\DriverController::class, 'statusupdate'])->name('status-update');
    Route::post('driver/switch', [App\Http\Controllers\DriverController::class, 'toggalSwitch'])->name('driver.switch');
    Route::post('driver/online-switch', [App\Http\Controllers\DriverController::class, 'toggalOnlineSwitch'])->name('driver.online.switch');
});

Route::group(['middleware' => ['permission:cms.index,cms.create,cms.edit']], function () {
    Route::get('cms', [App\Http\Controllers\CmsController::class, 'index'])->name('cms.index');
    Route::get('/cms/edit/{id}', [App\Http\Controllers\CmsController::class, 'edit'])->name('cms.edit');
    Route::put('cms/updateCms/{id}', [App\Http\Controllers\CmsController::class, 'updateCms'])->name('cms.updateCms');
    Route::get('/cms/create', [App\Http\Controllers\CmsController::class, 'create'])->name('cms.create');
    Route::post('/cms/store', [App\Http\Controllers\CmsController::class, 'store'])->name('cms.store');
    Route::get('/cms/destroycms/{id}', [App\Http\Controllers\CmsController::class, 'destroycms'])->name('cms.delete');
    Route::get('/cms/changeStatus/{id}', [App\Http\Controllers\CmsController::class, 'changeStatus'])->name('cms.changeStatus');
    Route::post('cms/switch', [App\Http\Controllers\CmsController::class, 'toggalSwitch']);
});

Route::group(['middleware' => ['permission:notifications.create, permission:notifications.delete, permission:notifications.index']], function () {
    Route::get('/notification', [App\Http\Controllers\AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notification/create', [App\Http\Controllers\AdminNotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notification/send', [App\Http\Controllers\AdminNotificationController::class, 'send'])->name('notifications.send');
    Route::get('/notification/delete/{id}', [App\Http\Controllers\AdminNotificationController::class, 'delete'])->name('notifications.delete');
});

Route::group(['middleware' => ['permission:ride.delete, permission:ride.show, permission:rides.index']], function () {
    Route::get('/rides/{id?}', [App\Http\Controllers\RidesController::class, 'index'])->name('rides.index');
    Route::get('/ride/delete/{rideid}', [App\Http\Controllers\RidesController::class, 'deleteRide'])->name('ride.delete');
    Route::get('/ride/show/{id}', [App\Http\Controllers\RidesController::class, 'show'])->name('ride.show');
    Route::get('/rides/filter', [App\Http\Controllers\RidesController::class, 'filterRides'])->name('rides.filter');
    Route::put('/rides/update/{id}', [App\Http\Controllers\RidesController::class, 'updateRide'])->name('rides.update');
});

Route::group(['middleware' => ['permission:parcel.delete, permission:parcel.show']], function () {
    Route::get('/parcel/{id?}', [App\Http\Controllers\ParcelOrdersController::class, 'index'])->name('parcel.index');
    Route::get('/parcel/delete/{rideid}', [App\Http\Controllers\ParcelOrdersController::class, 'deleteRide'])->name('parcel.delete');
    Route::get('/parcel/show/{id}', [App\Http\Controllers\ParcelOrdersController::class, 'show'])->name('parcel.show');
    Route::put('/parcel/update/{id}', [App\Http\Controllers\ParcelOrdersController::class, 'updateRide'])->name('parcel.update');
});

Route::group(['middleware' => ['permission:rental-orders.delete, permission:rental-orders.index, permission:rental-orders.show']], function () {
    Route::get('rental-orders', [App\Http\Controllers\RentalOrdersController::class, 'index'])->name('rental-orders.index');
    Route::put('rental-orders/update/{id}', [App\Http\Controllers\RentalOrdersController::class, 'update'])->name('rental-orders.update');
    Route::get('rental-orders/delete/{id}', [App\Http\Controllers\RentalOrdersController::class, 'delete'])->name('rental-orders.delete');
    Route::get('rental-orders/show/{id}', [App\Http\Controllers\RentalOrdersController::class, 'show'])->name('rental-orders.show');
});

Route::group(['middleware' => ['permission:vehicle-type.create, permission:vehicle-type.delete, permission:vehicle-type.edit, permission:vehicle-type.index']], function () {
    Route::get('/vehicle-type/', [App\Http\Controllers\VehicleController::class, 'index'])->name('vehicle-type.index');
    Route::get('/vehicle-type/create', [App\Http\Controllers\VehicleController::class, 'create'])->name('vehicle-type.create');
    Route::post('/vehicle-type/store', [App\Http\Controllers\VehicleController::class, 'store'])->name('vehicle-type.store');
    Route::get('/vehicle-type/edit/{id}', [App\Http\Controllers\VehicleController::class, 'edit'])->name('vehicle-type.edit');
    Route::put('/vehicle-type/update/{id}', [App\Http\Controllers\VehicleController::class, 'update'])->name('vehicle-type.update');
    Route::get('/vehicle-type/delete/{id}', [App\Http\Controllers\VehicleController::class, 'delete'])->name('vehicle-type.delete');
    Route::post('vehicle-type/switch', [App\Http\Controllers\VehicleController::class, 'toggalSwitch']);
});

Route::group(['middleware' => ['permission:userreport.index']], function () {
    Route::get('/reports/userreport', [App\Http\Controllers\ReportController::class, 'userreport'])->name('userreport.index');
    Route::get('/reports/downloadExcel', [App\Http\Controllers\ReportController::class, 'downloadExcel'])->name('userreport.downloadExcel');
});

Route::group(['middleware' => ['permission:driverreport.index']], function () {
    Route::get('/reports/driverreport', [App\Http\Controllers\ReportController::class, 'driverreport'])->name('driverreport.index');
    Route::get('/reports/downloadExcelDriver', [App\Http\Controllers\ReportController::class, 'downloadExcelDriver'])->name('driverreport.downloadExcelDriver');
});

Route::group(['middleware' => ['permission:ownerreport.index']], function () {
    Route::get('/reports/ownerreport', [App\Http\Controllers\ReportController::class, 'ownerreport'])->name('ownerreport.index');
    Route::get('/reports/downloadExcelOwner', [App\Http\Controllers\ReportController::class, 'downloadExcelOwner'])->name('ownerreport.downloadExcelOwner');
});

Route::group(['middleware' => ['permission:travelreport.index']], function () {
    Route::get('/reports/travelreport', [App\Http\Controllers\ReportController::class, 'travelreport'])->name('travelreport.index');
    Route::get('/reports/downloadExcelTravel', [App\Http\Controllers\ReportController::class, 'downloadExcelTravel'])->name('travelreport.downloadExcelTravel');
});

Route::group(['middleware' => ['permission:coupons.create, permission:coupons.delete, permission:coupons.edit, permission:coupons.index, permission:coupons.store, permission:coupons.update']], function () {
    Route::get('/coupons', [App\Http\Controllers\CouponController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/edit/{id}', [App\Http\Controllers\CouponController::class, 'edit'])->name('coupons.edit');
    Route::get('/coupons/create', [App\Http\Controllers\CouponController::class, 'create'])->name('coupons.create');
    Route::put('/coupons/update/{id}', [App\Http\Controllers\CouponController::class, 'updateDiscount'])->name('coupons.update');
    Route::post('/coupons/store', [App\Http\Controllers\CouponController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/delete/{id}', [App\Http\Controllers\CouponController::class, 'delete'])->name('coupons.delete');
    Route::get('/coupons/changeStatus/{id}', [App\Http\Controllers\CouponController::class, 'changeStatus'])->name('coupons.changeStatus');
    Route::post('coupon/switch', [App\Http\Controllers\CouponController::class, 'toggalSwitch']);
    Route::get('/coupon/{id}', [App\Http\Controllers\CouponController::class, 'index'])->name('restaurants.coupons');
    Route::get('/coupon/create/{id}', [App\Http\Controllers\CouponController::class, 'create']);
});

Route::group(['middleware' => ['permission:driversPayouts.create, permission:driversPayouts.index']], function () {
    Route::get('driversPayouts', [App\Http\Controllers\DriversPayoutController::class, 'index'])->name('driversPayouts.index');
    Route::get('driversPayouts/create', [App\Http\Controllers\DriversPayoutController::class, 'create'])->name('driversPayouts.create');
    Route::post('driversPayouts/store', [App\Http\Controllers\DriversPayoutController::class, 'store'])->name('driversPayouts.store');
    Route::any('driversPayouts/delete/{id}', [App\Http\Controllers\DriversPayoutController::class, 'delete'])->name('driversPayouts.delete');  
    Route::post('withdrawals/reject/{id}', [App\Http\Controllers\DriversPayoutController::class, 'reject'])->name('withdrawals.reject');
    Route::get('/drivers/{id}/bank-details', [App\Http\Controllers\DriversPayoutController::class, 'getBankDetails'])->name('drivers.bank.details');
    Route::post('/withdraw/accept', [App\Http\Controllers\DriversPayoutController::class, 'accept'])
    ->name('withdraw.accept');

});

Route::group(['middleware' => ['permission:walletstransaction.index']], function () {
    Route::get('walletstransaction', [App\Http\Controllers\TransactionController::class, 'index'])->name('walletstransaction.index');
});
Route::get('/walletstransaction/{id}', [App\Http\Controllers\TransactionController::class, 'index'])->name('walletstransaction.user');
Route::get('walletstransactions/driver/{id?}', [App\Http\Controllers\TransactionController::class, 'driverWallet'])->name('walletstransaction.driver');

Route::group(['middleware' => ['permission:currency.create, permission:currency.delete, permission:currency.edit, permission:currency.index, permission:currency.store, permission:currency.update']], function () {
    Route::get('/currency', [App\Http\Controllers\CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/currency/create', [App\Http\Controllers\CurrencyController::class, 'createCurrency'])->name('currency.create');
    Route::get('/currency/edit/{id}', [App\Http\Controllers\CurrencyController::class, 'edit'])->name('currency.edit');
    Route::put('/currency/update/{id}', [App\Http\Controllers\CurrencyController::class, 'update'])->name('currency.update');
    Route::post('/currency/store', [App\Http\Controllers\CurrencyController::class, 'store'])->name('currency.store');
    Route::get('/currency/changeStatus/{id}', [App\Http\Controllers\CurrencyController::class, 'changeStatus'])->name('currency.changeStatus');
    Route::get('/currency/delete/{id}', [App\Http\Controllers\CurrencyController::class, 'delete'])->name('currency.delete');
    Route::get('/currency/change/{id}', [App\Http\Controllers\CurrencyController::class, 'currencyEdit'])->name('edit_currency');
});

Route::group(['middleware' => ['permission:commission.edit']], function () {
    Route::get('/commission', [App\Http\Controllers\CommissionController::class, 'edit'])->name('commission.edit');
    Route::put('/commission/update/{id}', [App\Http\Controllers\CommissionController::class, 'update'])->name('commission.update');
    Route::get('/commission/changeStatus/{id}', [App\Http\Controllers\CommissionController::class, 'changeStatus'])->name('commission.changeStatus');
    Route::get('/commission/search', [App\Http\Controllers\CommissionController::class, 'searchCommision'])->name('commision.search');
    Route::post('/subscription-model-switch', [App\Http\Controllers\CommissionController::class, 'toggalSwitchSubscriptionModel'])->name('subscription-model.switch');
    Route::put('/bulk/commission/update', [App\Http\Controllers\CommissionController::class, 'bulkUpdate'])->name('bulk.commission.update');
});

Route::group(['middleware' => ['permission:tax.create, permission:tax.delete, permission:tax.edit, permission:tax.index']], function () {
    Route::get('/tax', [App\Http\Controllers\TaxController::class, 'index'])->name('tax.index');
    Route::get('/tax/create', [App\Http\Controllers\TaxController::class, 'create'])->name('tax.create');
    Route::post('/tax/store', [App\Http\Controllers\TaxController::class, 'store'])->name('tax.store');
    Route::get('/tax/edit/{id}', [App\Http\Controllers\TaxController::class, 'edit'])->name('tax.edit');
    Route::put('/tax/update/{id}', [App\Http\Controllers\TaxController::class, 'update'])->name('tax.update');
    Route::get('/tax/delete/{id}', [App\Http\Controllers\TaxController::class, 'delete'])->name('tax.delete');
    Route::get('/tax/changeStatus/{id}', [App\Http\Controllers\TaxController::class, 'changeStatus'])->name('tax.changeStatus');
    Route::get('/tax/search', [App\Http\Controllers\TaxController::class, 'searchTax'])->name('tax.search');
});

Route::group(['middleware' => ['permission:landing-page.edit']], function () {
    Route::get('/landing-template', [App\Http\Controllers\LandingPageTempController::class, 'index'])->name('landing-page.edit');
    Route::post('/landing-template/save', [App\Http\Controllers\LandingPageTempController::class, 'save'])->name('landing-page.update');
});
Route::group(['middleware' => ['permission:terms-condition.edit']], function () {
    Route::get('/terms-condition', [App\Http\Controllers\TermsAndConditionsController::class, 'index'])->name('terms-condition.edit');
    Route::put('/terms-condition/update/{id}', [App\Http\Controllers\TermsAndConditionsController::class, 'update'])->name('terms-condition.update');
});
Route::group(['middleware' => ['permission:privacy-policy.edit']], function () {
    Route::get('/privacy-policy', [App\Http\Controllers\TermsAndConditionsController::class, 'indexPrivacy'])->name('privacy-policy.edit');
    Route::put('/privacy-policy/update/{id}', [App\Http\Controllers\TermsAndConditionsController::class, 'updatePrivacy'])->name('privacy-policy.update');
});

Route::group(['middleware' => ['permission:driver-document.create, permission:driver-document.delete, permission:driver-document.edit, permission:driver-document.index,permission:driver-document.store,permission:driver-document.update']], function () {
    Route::get('/driver-document', [App\Http\Controllers\DriverDocumentController::class, 'index'])->name('driver-document.index');
    Route::get('/driver-document/create', [App\Http\Controllers\DriverDocumentController::class, 'create'])->name('driver-document.create');
    Route::post('/driver-document/store', [App\Http\Controllers\DriverDocumentController::class, 'storeDocument'])->name('driver-document.store');
    Route::get('/driver-document/edit/{id}', [App\Http\Controllers\DriverDocumentController::class, 'edit'])->name('driver-document.edit');
    Route::put('/driver-document/update/{id}', [App\Http\Controllers\DriverDocumentController::class, 'documentUpdate'])->name('driver-document.update');
    Route::get('/driver-document/delete/{id}', [App\Http\Controllers\DriverDocumentController::class, 'deleteDocument'])->name('driver-document.delete');
    Route::post('driver-document/switch', [App\Http\Controllers\DriverDocumentController::class, 'toggalSwitch'])->name('driver-document.switch');
});

Route::group(['middleware' => ['permission:email-template.edit, permission:email-template.index']], function () {
    Route::get('email-template', [App\Http\Controllers\EmailTemplateController::class, 'index'])->name('email-template.index');
    Route::get('email-template/edit/{id}', [App\Http\Controllers\EmailTemplateController::class, 'edit'])->name('email-template.edit');
    Route::put('email-template/update/{id}', [App\Http\Controllers\EmailTemplateController::class, 'update'])->name('email-template.update');
});

Route::group(['middleware' => ['permission:complaints.index, permission:complaints.show, permission:complaints.delete']], function () {
    Route::get('complaints', [App\Http\Controllers\ComplaintsController::class, 'index'])->name('complaints.index');
    Route::get('complaints/delete/{id}', [App\Http\Controllers\ComplaintsController::class, 'deleteComplaints'])->name('complaints.delete');
    Route::get('complaints/show/{id}', [App\Http\Controllers\ComplaintsController::class, 'show'])->name('complaints.show');
    Route::post('complaints/update', [App\Http\Controllers\ComplaintsController::class, 'update'])->name('complaints.update');
});

Route::group(['middleware' => ['permission:sos.delete, permission:sos.index, permission:sos.show']], function () {
    Route::get('sos', [App\Http\Controllers\SosController::class, 'index'])->name('sos.index');
    Route::get('/sos/show/{id}', [App\Http\Controllers\SosController::class, 'show'])->name('sos.show');
    Route::get('/sos/delete/{id}', [App\Http\Controllers\SosController::class, 'deleteSos'])->name('sos.delete');
    Route::put('/sos/update/{id}', [App\Http\Controllers\SosController::class, 'sosUpdate'])->name('sos.update');
});

Route::group(['middleware' => ['permission:car-model.index, permission:car-model.create, permission:car-model.delete, permission:car-model.edit, permission:car-model.update']], function () {
    Route::get('/car-model', [App\Http\Controllers\CarModelController::class, 'index'])->name('car-model.index');
    Route::get('/car-model/create', [App\Http\Controllers\CarModelController::class, 'create'])->name('car-model.create');
    Route::get('/car-model/edit/{id}', [App\Http\Controllers\CarModelController::class, 'edit'])->name('car-model.edit');
    Route::get('/car-model/delete', [App\Http\Controllers\CarModelController::class, 'deleteCarModel'])->name('car-model.delete');
    Route::put('car-model/update/{id}', [App\Http\Controllers\CarModelController::class, 'UpdateCarModel'])->name('car-model.update');
    Route::post('/car-model/storecarmodel', [App\Http\Controllers\CarModelController::class, 'storecarmodel'])->name('car-model.storecarmodel');
    Route::post('car-model/switch', [App\Http\Controllers\CarModelController::class, 'toggalSwitch']);
});

Route::group(['middleware' => ['permission:brand.index,permission:brand.create,permission:brand.edit,permission:brand.delete']], function () {
    Route::get('brands', [App\Http\Controllers\BrandController::class, 'index'])->name('brand.index');
    Route::get('brands/create', [App\Http\Controllers\BrandController::class, 'createCurrency'])->name('brand.create');
    Route::post('brands/create', [App\Http\Controllers\BrandController::class, 'store'])->name('brand.store');
    Route::get('brands/edit/{id}', [App\Http\Controllers\BrandController::class, 'edit'])->name('brand.edit');
    Route::put('brands/update/{id}', [App\Http\Controllers\BrandController::class, 'update'])->name('brand.update');
    Route::get('brands/delete/{id}', [App\Http\Controllers\BrandController::class, 'deleteBrand'])->name('brand.delete');
    Route::post('brand/switch', [App\Http\Controllers\BrandController::class, 'toggalSwitch']);
});

Route::post('currency/switch', [App\Http\Controllers\CurrencyController::class, 'toggalSwitch'])->name('currency.switch');
Route::post('commission/switch', [App\Http\Controllers\CommissionController::class, 'toggalSwitch']);
Route::post('tax/switch', [App\Http\Controllers\TaxController::class, 'toggalSwitch']);
Route::get('/payoutRequest', [App\Http\Controllers\PayoutRequestController::class, 'payout'])->name('payoutRequests');
Route::get('/payoutRequest/{id}', [App\Http\Controllers\PayoutRequestController::class, 'payout'])->name('payoutRequests.view');
Route::post('driver/getbankdetails', [App\Http\Controllers\PayoutRequestController::class, 'getBankDetails']);
Route::post('withdrawal/accept', [App\Http\Controllers\PayoutRequestController::class, 'acceptWithdrawal']);
Route::post('withdrawal/reject', [App\Http\Controllers\PayoutRequestController::class, 'rejectWithdrawal']);

Route::group(['middleware' => ['permission:dispatcher-users.create, permission:dispatcher-users.delete, permission:dispatcher-users.edit, permission:dispatcher-users.index, permission:dispatcher-users.show, permission:dispatcher-users.store, permission:dispatcher-users.update']], function () {
    Route::get('/dispatcher-users', [App\Http\Controllers\DispatcherController::class, 'index'])->name('dispatcher-users.index');
    Route::get('/dispatcher-users/create', [App\Http\Controllers\DispatcherController::class, 'createUser'])->name('dispatcher-users.create');
    Route::post('/dispatcher-users/storeuser', [App\Http\Controllers\DispatcherController::class, 'storeUser'])->name('dispatcher-users.store');
    Route::get('/dispatcher-users/edit/{id}', [App\Http\Controllers\DispatcherController::class, 'editUser'])->name('dispatcher-users.edit');
    Route::get('/dispatcher-users/delete/{id}', [App\Http\Controllers\DispatcherController::class, 'deleteUser'])->name('dispatcher-users.delete');
    Route::put('dispatcher-users/update/{id}', [App\Http\Controllers\DispatcherController::class, 'userUpdate'])->name('dispatcher-users.update');
    Route::post('/switch', [App\Http\Controllers\UserController::class, 'toggalSwitch']);
    Route::post('/dispatcher-users-switch', [App\Http\Controllers\DispatcherController::class, 'toggalSwitch']);
    Route::get('/dispatcher-users/show/{id}', [App\Http\Controllers\DispatcherController::class, 'userShow'])->name('dispatcher-users.show');
    Route::get('/dispatcher-users/changestatus/{id}', [App\Http\Controllers\DispatcherController::class, 'userChangeStatus'])->name('dispatcher-users.changestatus');
});

Route::group(['middleware' => ['permission:live-tracking.index']], function () {
    Route::get('/map', [App\Http\Controllers\MapController::class, 'index'])->name('live-tracking.index');
});

Route::group(['middleware' => ['permission:parcel-category.index, parcel-category.create, permission:parcel-category.delete, permission:parcel-category.edit']], function () {
    Route::get('/parcel-category', [App\Http\Controllers\ParcelCategoryController::class, 'index'])->name('parcel-category.index');
    Route::get('/parcel-category/create', [App\Http\Controllers\ParcelCategoryController::class, 'create'])->name('parcel-category.create');
    Route::post('/parcel-category/store', [App\Http\Controllers\ParcelCategoryController::class, 'store'])->name('parcel-category.store');
    Route::get('/parcel-category/edit/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'edit'])->name('parcel-category.edit');
    Route::get('/parcel-category/delete/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'delete'])->name('parcel-category.delete');
    Route::put('parcel-category/update/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'update'])->name('parcel-category.update');
    Route::post('/parcel-category-switch', [App\Http\Controllers\ParcelCategoryController::class, 'toggalSwitch']);
    Route::get('/parcel-category/changestatus/{id}', [App\Http\Controllers\ParcelCategoryController::class, 'changeStatus'])->name('parcel-category.changestatus');
});

Route::group(['middleware' => ['permission:zone.create, permission:zone.delete, permission:zone.edit, permission:zone.index']], function () {
    Route::get('zone', [App\Http\Controllers\ZoneController::class, 'index'])->name('zone.index');
    Route::get('zone/create', [App\Http\Controllers\ZoneController::class, 'create'])->name('zone.create');
    Route::post('zone/store', [App\Http\Controllers\ZoneController::class, 'store'])->name('zone.store');
    Route::get('zone/edit/{id}', [App\Http\Controllers\ZoneController::class, 'edit'])->name('zone.edit');
    Route::put('zone/update/{id}', [App\Http\Controllers\ZoneController::class, 'update'])->name('zone.update');
    Route::get('zone/delete/{id}', [App\Http\Controllers\ZoneController::class, 'delete'])->name('zone.delete');
    Route::post('zone/switch', [App\Http\Controllers\ZoneController::class, 'toggalSwitch'])->name('zone.switch');
});

Route::group(['middleware' => ['permission:banners.index, permission:banners.create, permission:banners.edit, permission:banners.delete']], function () {
    Route::get('/banners', [App\Http\Controllers\BannersController::class, 'index'])->name('banners.index');
    Route::get('/banners/create', [App\Http\Controllers\BannersController::class, 'create'])->name('banners.create');
    Route::post('/banners/store', [App\Http\Controllers\BannersController::class, 'store'])->name('banners.store');
    Route::get('/banners/edit/{id}', [App\Http\Controllers\BannersController::class, 'edit'])->name('banners.edit');
    Route::get('/banners/delete/{id}', [App\Http\Controllers\BannersController::class, 'delete'])->name('banners.delete');
    Route::put('banners/update/{id}', [App\Http\Controllers\BannersController::class, 'update'])->name('banners.update');
});
Route::post('/banners-switch', [App\Http\Controllers\BannersController::class, 'toggalSwitch']);
Route::get('/banners/changestatus/{id}', [App\Http\Controllers\BannersController::class, 'changeStatus'])->name('banners.changestatus');

Route::group(['middleware' => ['permission:on-boarding.edit, permission:on-boarding.index']], function () {
    Route::get('/on-boarding', [App\Http\Controllers\OnBoardingController::class, 'index'])->name('on-boarding.index');
    Route::get('/on-boarding/edit/{id}', [App\Http\Controllers\OnBoardingController::class, 'edit'])->name('on-boarding.edit');
    Route::put('/on-boarding/update/{id}', [App\Http\Controllers\OnBoardingController::class, 'update'])->name('on-boarding.update');
});

Route::group(['middleware' => ['permission:subscription-plans.create, permission:subscription-plans.delete, permission:subscription-plans.edit, permission:subscription-plans.index, subscription-history.index']], function () {
    Route::get('/subscription-plans', [App\Http\Controllers\SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
    Route::get('/subscription-plans/create', [App\Http\Controllers\SubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
    Route::post('/subscription-plans/store', [App\Http\Controllers\SubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
    Route::get('/subscription-plans/edit/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
    Route::put('subscription-plans/update/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
    Route::get('/subscription-plans/delete/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'delete'])->name('subscription-plans.delete');
    Route::get('/driver/subscription-plan/history', [App\Http\Controllers\SubscriptionPlanController::class, 'SubscriptionHistory'])->name('subscription-history.index');
});

Route::group(['middleware' => ['permission:subscription-history.delete']], function () {
    Route::get('/subscription-history/delete/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'deleteHistory'])->name('subscription-history.delete');
});
Route::group(['middleware' => ['permission:current-subscriber.list']], function () {
    Route::get('/current-subscriber/{id}', [App\Http\Controllers\SubscriptionPlanController::class, 'currentSubscriberList'])->name('current-subscriber.list');
});

Route::post('/subscription-plans-switch', [App\Http\Controllers\SubscriptionPlanController::class, 'toggalSwitch']);
Route::post('/get-plan-detail', [App\Http\Controllers\DriverController::class, 'getPlanDetail'])->name('get-plan-detail');
Route::post('/subscription-checkout', [App\Http\Controllers\DriverController::class, 'subscriptionCheckout'])->name('subscription-checkout');
Route::put('subscription-limit/update/{id}', [App\Http\Controllers\DriverController::class, 'updateLimit'])->name('subscription-limit.update');
Route::post('/driver/send-notification', [App\Http\Controllers\DriverController::class, 'sendDocumentNotification'])->name('driver.sendNotification');
Route::get('/export/{type}/{model}', [App\Http\Controllers\ExportController::class, 'export'])->name('export.data');

Route::group(['middleware' => ['permission:owners.create, permission:owners.delete, permission:owners.edit, permission:owners.index, permission:owners.show']], function () {
    Route::get('/owners', [App\Http\Controllers\OwnersController::class, 'index'])->name('owners.index');
    Route::get('/owners/approved', [App\Http\Controllers\OwnersController::class, 'index'])->name('owners.approved');
    Route::get('/owners/pending', [App\Http\Controllers\OwnersController::class, 'index'])->name('owners.pending');
    Route::get('/owners/create', [App\Http\Controllers\OwnersController::class, 'create'])->name('owners.create');
    Route::post('/owners/store', [App\Http\Controllers\OwnersController::class, 'store'])->name('owners.store');
    Route::get('/owners/delete/{id}', [App\Http\Controllers\OwnersController::class, 'deleteOwner'])->name('owners.delete');
    Route::put('owners/update/{id}', [App\Http\Controllers\OwnersController::class, 'updateOwner'])->name('owners.update');
    Route::get('/owners/edit/{id}', [App\Http\Controllers\OwnersController::class, 'edit'])->name('owners.edit');
    Route::post('owner/switch', [App\Http\Controllers\OwnersController::class, 'toggalSwitch']);
    Route::get('/owners/show/{id}', [App\Http\Controllers\OwnersController::class, 'show'])->name('owners.show');
});

Route::group(['middleware' => ['permission:owner-vehicle.create, permission:owner-vehicle.edit']], function () {
    Route::post('vehicle/create/{ownerId}', [App\Http\Controllers\OwnersController::class, 'createVehicle'])->name('owner-vehicle.create');
    Route::get('owner-vehicle/edit/{id}', [App\Http\Controllers\OwnersController::class, 'editVehicle'])->name('owner-vehicle.edit');
    Route::post('owner-vehicle/update/{id}', [App\Http\Controllers\OwnersController::class, 'updateVehicle'])->name('owner-vehicle.update');
});

Route::get('vehicle/remove/driver/{vehicleId}', [App\Http\Controllers\OwnersController::class, 'removeDriver'])->name('vehicle.remove.driver');
Route::post('vehicle/assign/driver', [App\Http\Controllers\OwnersController::class, 'assignDriver'])->name('vehicle.assign.driver');

Route::group(['middleware' => ['permission:fleetdrivers.index']], function () {
    Route::get('/fleet-drivers', [App\Http\Controllers\DriverController::class, 'allFleetDriver'])->name('fleetdrivers.index');
    Route::get('/fleet-drivers/approved', [App\Http\Controllers\DriverController::class, 'allFleetDriver'])->name('fleetdrivers.approved');
    Route::get('/fleet-drivers/pending', [App\Http\Controllers\DriverController::class, 'allFleetDriver'])->name('fleetdrivers.pending');
});

Route::group(['middleware' => ['permission:rental-packages.create, permission:rental-packages.delete,permission:rental-packages.edit, permission:rental-packages.index']], function () {
    Route::get('/rental-packages', [App\Http\Controllers\RentalPackageController::class, 'index'])->name('rental-packages.index');
    Route::get('/rental-packages/create', [App\Http\Controllers\RentalPackageController::class, 'create'])->name('rental-packages.create');
    Route::post('/rental-packages/store', [App\Http\Controllers\RentalPackageController::class, 'store'])->name('rental-packages.store');
    Route::get('/rental-packages/edit/{id}', [App\Http\Controllers\RentalPackageController::class, 'edit'])->name('rental-packages.edit');
    Route::put('rental-packages/update/{id}', [App\Http\Controllers\RentalPackageController::class, 'update'])->name('rental-packages.update');
    Route::get('/rental-packages/delete/{id}', [App\Http\Controllers\RentalPackageController::class, 'delete'])->name('rental-packages.delete');
    Route::post('/rental-packages-switch', [App\Http\Controllers\RentalPackageController::class, 'toggalSwitch']);
});

Route::group(['middleware' => ['permission:roles.create, permission:roles.delete,permission:roles.edit, permission:roles.index']], function () {
    Route::get('roles', [App\Http\Controllers\RolesController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [App\Http\Controllers\RolesController::class, 'create'])->name('roles.create');
    Route::post('roles/store', [App\Http\Controllers\RolesController::class, 'store'])->name('roles.store');
    Route::get('roles/delete/{id}', [App\Http\Controllers\RolesController::class, 'delete'])->name('roles.delete');
    Route::get('roles/edit/{id}', [App\Http\Controllers\RolesController::class, 'edit'])->name('roles.edit');
    Route::post('roles/update/{id}', [App\Http\Controllers\RolesController::class, 'update'])->name('roles.update');
    Route::post('/roles/delete-multiple', [RoleController::class, 'deleteMultiple'])->name('roles.deleteMultiple');

});

Route::group(['middleware' => ['permission:admin-users.index, permission:admin-users.create, permission:admin-users.edit, permission:admin-users.delete,']], function () {
    Route::get('admin-users', [App\Http\Controllers\AdminUserController::class, 'index'])->name('admin-users.index');
    Route::get('admin-users/create', [App\Http\Controllers\AdminUserController::class, 'create'])->name('admin-users.create');
    Route::post('admin-users/store', [App\Http\Controllers\AdminUserController::class, 'store'])->name('admin-users.store');
    Route::get('admin-users/edit/{id}', [App\Http\Controllers\AdminUserController::class, 'edit'])->name('admin-users.edit');
    Route::post('admin-users/update/{id}', [App\Http\Controllers\AdminUserController::class, 'update'])->name('admin-users.update');
    Route::get('admin-users/delete/{id}', [App\Http\Controllers\AdminUserController::class, 'delete'])->name('admin-users.delete');
});

Route::prefix('settings')->group(function () {
    Route::group(['middleware' => ['permission:general-settings.edit']], function () {
        Route::get('general', [App\Http\Controllers\SettingsController::class, 'general'])->name('general-settings.edit');
    });
    Route::post('general/update/{id}', [App\Http\Controllers\SettingsController::class, 'updateGeneral'])->name('general-settings.update');
    Route::get('payment/stripe', [App\Http\Controllers\SettingsController::class, 'stripe'])->name('payment.stripe');
    Route::put('payment/stripeUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'stripeUpdate'])->name('payment.stripeUpdate');
    Route::get('payment/applepay', [App\Http\Controllers\SettingsController::class, 'applepay'])->name('payment.applepay');
    Route::put('payment/applepayUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'applepayUpdate'])->name('payment.applepayUpdate');
    Route::get('payment/razorpay', [App\Http\Controllers\SettingsController::class, 'razorpay'])->name('payment.razorpay');
    Route::put('payment/razorpayUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'razorpayUpdate'])->name('payment.razorpayUpdate');
    Route::get('payment/cod', [App\Http\Controllers\SettingsController::class, 'cod'])->name('payment.cod');
    Route::put('payment/codUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'codUpdate'])->name('payment.codUpdate');
    Route::get('payment/paypal', [App\Http\Controllers\SettingsController::class, 'paypal'])->name('payment.paypal');
    Route::put('payment/paypalUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'paypalUpdate'])->name('payment.paypalUpdate');
    Route::get('payment/wallet', [App\Http\Controllers\SettingsController::class, 'wallet'])->name('payment.wallet');
    Route::put('payment/walletUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'walletUpdate'])->name('payment.walletUpdate');
    Route::get('payment/payfast', [App\Http\Controllers\SettingsController::class, 'payfast'])->name('payment.payfast');
    Route::put('payment/payfastUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'payfastUpdate'])->name('payment.payfastUpdate');
    Route::get('payment/paystack', [App\Http\Controllers\SettingsController::class, 'paystack'])->name('payment.paystack');
    Route::put('payment/paystackUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'paystackUpdate'])->name('payment.paystackUpdate');
    Route::get('payment/flutterwave', [App\Http\Controllers\SettingsController::class, 'flutterwave'])->name('payment.flutterwave');
    Route::put('payment/flutterUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'flutterUpdate'])->name('payment.flutterUpdate');
    Route::get('payment/mercadopago', [App\Http\Controllers\SettingsController::class, 'mercadopago'])->name('payment.mercadopago');
    Route::put('payment/mercadopago/{id}', [App\Http\Controllers\SettingsController::class, 'mercadopagoUpdate'])->name('payment.mercadopagoUpdate');
    Route::get('payment/xendit', [App\Http\Controllers\SettingsController::class, 'xendit'])->name('payment.xendit');
    Route::put('payment/xenditUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'xenditUpdate'])->name('payment.xenditUpdate');
    Route::get('payment/orangepay', [App\Http\Controllers\SettingsController::class, 'orangepay'])->name('payment.orangepay');
    Route::put('payment/orangepayUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'orangepayUpdate'])->name('payment.orangepayUpdate');
    Route::get('payment/midtrans', [App\Http\Controllers\SettingsController::class, 'midtrans'])->name('payment.midtrans');
    Route::put('payment/midtransUpdate/{id}', [App\Http\Controllers\SettingsController::class, 'midtransUpdate'])->name('payment.midtransUpdate');
});

Route::get('/get-driver-location/{driverId}', [App\Http\Controllers\DriverController::class, 'getDriverLocation']);   