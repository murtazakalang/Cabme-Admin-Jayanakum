<?php
use App\Http\Controllers\API\v1\AddAmountController;
use App\Http\Controllers\API\v1\AddComplaintsController;
use App\Http\Controllers\API\v1\BankAccountDetailsController;
use App\Http\Controllers\API\v1\BrandListController;
use App\Http\Controllers\API\v1\ChangeStatusControlller;
use App\Http\Controllers\API\v1\ChangeStatusForpaymentController;
use App\Http\Controllers\API\v1\RideCompleteController;
use App\Http\Controllers\API\v1\RideConfirmController;
use App\Http\Controllers\API\v1\DeleteUserController;
use App\Http\Controllers\API\v1\DiscountController;
use App\Http\Controllers\API\v1\DocumentsController;
use App\Http\Controllers\API\v1\DriverController;
use App\Http\Controllers\API\v1\DriversVehicleController;
use App\Http\Controllers\API\v1\DriverWithdrawalsController;
use App\Http\Controllers\API\v1\ExistingUserController;
use App\Http\Controllers\API\v1\GetDriverWithdrawalsController;
use App\Http\Controllers\API\v1\GetFcmController;
use App\Http\Controllers\API\v1\GetProfileByPhoneController;
use App\Http\Controllers\API\v1\LaunguageController;
use App\Http\Controllers\API\v1\ModelListController;
use App\Http\Controllers\API\v1\RideOnRideController;
use App\Http\Controllers\API\v1\payments\PaymentController;
use App\Http\Controllers\API\v1\payments\RazorPayController;
use App\Http\Controllers\API\v1\PaymentSettingController;
use App\Http\Controllers\API\v1\PositionController;
use App\Http\Controllers\API\v1\PrivacyPolicyController;
use App\Http\Controllers\API\v1\RideRegisterController;
use App\Http\Controllers\API\v1\ResertPasswordController;
use App\Http\Controllers\API\v1\RideRejectController;
use App\Http\Controllers\API\v1\SettingsController;
use App\Http\Controllers\API\v1\SosController;
use App\Http\Controllers\API\v1\TermsofConditionController;
use App\Http\Controllers\API\v1\UpdateFcmController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\UserLoginController;
use App\Http\Controllers\API\v1\UsermdpController;
use App\Http\Controllers\API\v1\VehicleController;
use App\Http\Controllers\API\v1\GetUserReferralCode;
use App\Http\Controllers\API\v1\ParcelCategoryController;
use App\Http\Controllers\API\v1\ParcelOrdersController;
use App\Http\Controllers\API\v1\ParcelRegisterController;
use App\Http\Controllers\API\v1\ParcelConfirmController;
use App\Http\Controllers\API\v1\ParcelOnRideController;
use App\Http\Controllers\API\v1\ParcelCompleteController;
use App\Http\Controllers\API\v1\ParcelRejectController;
use App\Http\Controllers\API\v1\ParcelSearchController;
use App\Http\Controllers\API\v1\ZoneController;
use App\Http\Controllers\API\v1\BannersController;
use App\Http\Controllers\API\v1\UserProfileUpdateController;
use App\Http\Controllers\API\v1\OnBoardingController;
use App\Http\Controllers\API\v1\SubscriptionPlanController;
use App\Http\Controllers\API\v1\WalletHistoryController;
use App\Http\Controllers\API\v1\BookingsController;
use App\Http\Controllers\API\v1\ReviewController;
use App\Http\Controllers\API\v1\RentalPackagesController;
use App\Http\Controllers\API\v1\RentalRegisterController;
use App\Http\Controllers\API\v1\RentalConfirmController;
use App\Http\Controllers\API\v1\RentalOnRideController;
use App\Http\Controllers\API\v1\RentalRejectController;
use App\Http\Controllers\API\v1\RentalCompleteController;
use App\Http\Controllers\API\v1\RentalOrdersController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['apiKeyAuth']], function () {
    /*Guest Request*/
    Route::post('v1/user/', [UserController::class, 'register']);
    Route::post('v1/user-login/', [UserLoginController::class, 'login']);
    Route::post('v1/logout/', [UserLoginController::class, 'logout']);
    Route::post('v1/existing-user/', [ExistingUserController::class, 'getData']);
    Route::get('v1/language/', [LaunguageController::class, 'getData']);
    Route::get('v1/privacy-policy/', [PrivacyPolicyController::class, 'getData']);
    Route::get('v1/terms-of-condition/', [TermsofConditionController::class, 'getData']);
    Route::get('v1/settings/', [SettingsController::class, 'getData']);
    Route::post('v1/profilebyphone/', [GetProfileByPhoneController::class, 'getData']);
    Route::post('v1/driver-documents-update/', [DocumentsController::class, 'updateDriverDocuments']);
    Route::post('v1/driver-documents/', [DocumentsController::class, 'getDriverDocuments']);
    Route::get('v1/zone/', [ZoneController::class, 'getData']);
    Route::get('v1/on-boarding/', [OnBoardingController::class, 'getData']);
    Route::post('v1/reset-password/', [ResertPasswordController::class, 'resetPassword']);
    Route::post('v1/reset-password-otp/', [ResertPasswordController::class, 'resetPasswordOtp']);
});

Route::group(['middleware' => ['accessKeyAuth']], function () {
    /*Auth Request*/
    Route::get('v1/Vehicle-category/', [VehicleController::class, 'getVehicleCategoryData']);
    Route::post('v1/user-delete/', [DeleteUserController::class, 'deleteuser']);
    Route::post('v1/discount-list/', [DiscountController::class, 'discountList']);
    Route::get('v1/vehicle-driver/', [DriversVehicleController::class, 'getData']);
    Route::post('v1/fcm-token/', [GetFcmController::class, 'getData']);
    Route::get('v1/payment-settings/', [PaymentSettingController::class, 'getData']);
    Route::post('v1/model/', [ModelListController::class, 'getData']);
    Route::get('v1/brand/', [BrandListController::class, 'getData']);
    Route::post('v1/add-bank-details/', [BankAccountDetailsController::class, 'register']);
    Route::post('v1/bank-details/', [BankAccountDetailsController::class, 'getData']);
    Route::post('v1/withdrawals/', [DriverWithdrawalsController::class, 'Withdrawals']);
    Route::post('v1/withdrawals-list/', [GetDriverWithdrawalsController::class, 'WithdrawalsList']);
    Route::post('v1/update-user-mdp/', [UsermdpController::class, 'UpdateUsermdp']);
    Route::post('v1/change-status/', [ChangeStatusControlller::class, 'changeStatus']);
    
    Route::post('v1/update-fcm/', [UpdateFcmController::class, 'updatefcm']);
    Route::post('v1/storesos/', [SosController::class, 'storeSos']);
    Route::post('v1/change-booking-payment-method/', [ChangeStatusForpaymentController::class, 'ChangeBookingStatus']);

    /*Payments*/
    Route::post('v1/payments/getpaytmchecksum', [PaymentController::class, 'getPaytmChecksum']);
    Route::post('v1/payments/validatechecksum', [PaymentController::class, 'validateChecksum']);
    Route::post('v1/payments/initiatepaytmpayment', [PaymentController::class, 'initiatePaytmPayment']);
    Route::post('v1/payments/paytmpaymentcallback', [PaymentController::class, 'paytmPaymentcallback']);
    Route::post('v1/payments/paypalclientid', [PaymentController::class, 'getPaypalClienttoken']);
    Route::post('v1/payments/paypaltransaction', [PaymentController::class, 'createBraintreePayment']);
    Route::post('v1/payments/stripepaymentintent', [PaymentController::class, 'createStripePaymentIntent']);
    Route::post('v1/payments/razorpay/createorder', [RazorPayController::class, 'createOrderid']);
    /*End Payments*/

    Route::post('v1/complaints/', [AddComplaintsController::class, 'register']);
    Route::post('v1/complaintsList/', [AddComplaintsController::class, 'index']);
    Route::get('v1/get-referral/', [GetUserReferralCode::class, 'getData']);
    Route::get('v1/get-parcel-category/', [ParcelCategoryController::class, 'getData']);
    
    Route::get('v1/get-banners/', [BannersController::class, 'getData']);
    Route::post('v1/update-user-profile/', [UserProfileUpdateController::class, 'update']);
    Route::post('v1/get-subscription-plans/', [SubscriptionPlanController::class, 'getPlanList']);
    Route::post('v1/set-subscription/', [SubscriptionPlanController::class, 'setData']);
    Route::post('v1/get-subscription-history/', [SubscriptionPlanController::class, 'getSubscriptionHistory']);
    
    Route::post('v1/add-owner-driver/', [UserController::class, 'addOwnerDriver']);
    Route::post('v1/get-owner-driver/', [DriverController::class, 'getOwnerDriver']);
    Route::post('v1/delete-owner-driver/', [DriverController::class, 'deleteOwnerDriver']);
    Route::post('v1/get-owner-dashboard/', [DriverController::class, 'getOwnerDashboard']);
    Route::post('v1/get-driver-details/', [DriverController::class, 'getDriverDetail']);   
    
    Route::post('v1/remove-driver-vehicle/', [VehicleController::class, 'removeDriverVehicle']);
    Route::post('v1/vehicle/', [VehicleController::class, 'register']);
    Route::post('v1/owner-vehicle-register/', [VehicleController::class, 'ownerVehicleRegister']);
    Route::post('v1/get-owner-vehicle/', [VehicleController::class, 'getOwnerVehicle']);

    Route::post('v1/ride-book/', [RideRegisterController::class, 'rideBook']);
    Route::post('v1/confirm-requete/', [RideConfirmController::class, 'confirmRequest']);
    Route::post('v1/onride-requete/', [RideOnRideController::class, 'confirmRide']);
    Route::post('v1/set-rejected-requete/', [RideRejectController::class, 'rejectedRequest']);
    Route::post('v1/set-cancelled-requete/', [RideRejectController::class, 'cancelledRequest']);
    Route::post('v1/complete-requete/', [RideCompleteController::class, 'completeRequest']);

    Route::post('v1/search-driver-parcel-order/', [ParcelSearchController::class, 'getData']);    
    Route::post('v1/parcel-register', [ParcelRegisterController::class, 'register']);
    Route::post('v1/parcel-confirm', [ParcelConfirmController::class, 'confirmRequest']);
    Route::post('v1/parcel-onride', [ParcelOnRideController::class, 'onrideRequest']);
    Route::post('v1/parcel-canceled', [ParcelRejectController::class, 'cancelRequest']);
    Route::post('v1/parcel-complete', [ParcelCompleteController::class, 'completeRequest']);    
    Route::post('v1/get-driver-parcel-orders', [ParcelOrdersController::class, 'getDriverParcel']);
    Route::post('v1/get-user-parcel-orders', [ParcelOrdersController::class, 'getUserParcel']);
    Route::post('v1/get-parcel-detail', [ParcelOrdersController::class, 'getParcelDetail']);
    
    Route::post('v1/rental-register', [RentalRegisterController::class, 'register']);
    Route::post('v1/rental-confirm', [RentalConfirmController::class, 'confirmRequest']);
    Route::post('v1/rental-onride', [RentalOnRideController::class, 'onrideRequest']);
    Route::post('v1/rental-rejected', [RentalRejectController::class, 'rejectRequest']);
    Route::post('v1/rental-canceled', [RentalRejectController::class, 'cancelRequest']);
    Route::post('v1/rental-setfinalkm', [RentalCompleteController::class, 'setFinalKmRequest']);    
    Route::post('v1/rental-complete', [RentalCompleteController::class, 'completeRequest']);    
    Route::post('v1/search-driver-rental-order/', [RentalOrdersController::class, 'getData']);    
    Route::post('v1/get-recent-driver-rental-order/', [RentalOrdersController::class, 'getRecentData']); 
    Route::post('v1/get-rental-booking-details/', [BookingsController::class, 'getRentalBookingDetails']);   

    Route::post('v1/submit-review/', [ReviewController::class, 'submitReview']);
    Route::post('v1/get-review/', [ReviewController::class, 'getReview']);

    Route::post('v1/get-booking-details/', [BookingsController::class, 'getBookingDetails']);
    Route::post('v1/get-booking-list/', [BookingsController::class, 'getBookingList']);
    Route::post('v1/user-recent-ride/', [BookingsController::class, 'getUserRecentRide']);
    Route::post('v1/driver-recent-ride/', [BookingsController::class, 'getDriverRecentRide']);
    
    Route::post('v1/update-position/', [PositionController::class, 'updatePosition']);    
    Route::post('v1/amount/', [AddAmountController::class, 'register']);
    Route::post('v1/get-wallet-history/', [WalletHistoryController::class, 'getWalletHistory']);
    Route::post('v1/get-rental-packages/', [RentalPackagesController::class, 'getList']);
    Route::get('v1/get-service-json/', [UpdateFcmController::class, 'getServiceJson']);
});