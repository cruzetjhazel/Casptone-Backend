<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\PhotographerApplicationController as AdminPhotographerApplicationController;
use App\Http\Controllers\Api\PhotographerApplicationController;
use App\Http\Controllers\Api\Admin\PhotographerPortfolioController as AdminPhotographerPortfolioController;
use App\Http\Controllers\Api\Photographer\PortfolioController;
use App\Http\Controllers\Api\Photographer\ProfileController;
use App\Http\Controllers\Api\PublicPhotographerController;
use App\Http\Controllers\Api\Photographer\AddOnController;
use App\Http\Controllers\Api\Photographer\CustomPackageController;
use App\Http\Controllers\Api\Photographer\PackageController;
use App\Http\Controllers\Api\PublicPhotographerAddOnController;
use App\Http\Controllers\Api\PublicPhotographerPackageController;
use App\Http\Controllers\Api\Photographer\AvailabilityWindowController;
use App\Http\Controllers\Api\Photographer\BlockedDateController;
use App\Http\Controllers\Api\PublicPhotographerAvailabilityController;
use App\Http\Controllers\Api\Client\BookingController as ClientBookingController;
use App\Http\Controllers\Api\Photographer\BookingController as PhotographerBookingController;
use App\Http\Controllers\Api\Photographer\PaymentConfigController;
use App\Http\Controllers\Api\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Api\Photographer\PaymentController as PhotographerPaymentController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Photographer\PaymentReferenceController;
use App\Http\Controllers\Api\NotificationController;

Route::prefix('auth')->group(function () {
    Route::post('register-client', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::post('auth/register-photographer', [AuthController::class, 'registerPhotographer']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('photographer')->group(function () {
        Route::get('application', [PhotographerApplicationController::class, 'show']);
        Route::patch('application', [PhotographerApplicationController::class, 'update']);
        Route::post('application/submit', [PhotographerApplicationController::class, 'submit']);
        Route::post('application/reapply', [PhotographerApplicationController::class, 'reapply']);
    });

    Route::prefix('admin/photographer-applications')->group(function () {
        Route::get('/', [AdminPhotographerApplicationController::class, 'index']);
        Route::get('{photographerApplication}', [AdminPhotographerApplicationController::class, 'show']);
        Route::get('{photographerApplication}/documents/{type}', [AdminPhotographerApplicationController::class, 'downloadDocument']);
        Route::post('{photographerApplication}/approve', [AdminPhotographerApplicationController::class, 'approve']);
        Route::post('{photographerApplication}/reject', [AdminPhotographerApplicationController::class, 'reject']);
        Route::post('{photographerApplication}/request-revision', [AdminPhotographerApplicationController::class, 'requestRevision']);
    });
});

Route::get('photographers/{user}', [PublicPhotographerController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('photographer')->group(function () {
        Route::post('profile', [ProfileController::class, 'store']);
        Route::get('profile', [ProfileController::class, 'show']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::get('profile/completeness', [ProfileController::class, 'completeness']);

        Route::get('portfolio', [PortfolioController::class, 'index']);
        Route::post('portfolio', [PortfolioController::class, 'store']);
        Route::post('portfolio/{portfolioImage}/archive', [PortfolioController::class, 'archive']);
        Route::post('portfolio/{portfolioImage}/restore', [PortfolioController::class, 'restore']);
        Route::delete('portfolio/{portfolioImage}', [PortfolioController::class, 'destroy']);
    });

    Route::post('admin/photographer-portfolio-images/{portfolioImage}/archive', [AdminPhotographerPortfolioController::class, 'archive']);
});
Route::get('photographers/{user}/packages', [PublicPhotographerPackageController::class, 'index']);
Route::get('photographers/{user}/add-ons', [PublicPhotographerAddOnController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('photographer')->group(function () {
        Route::apiResource('packages', PackageController::class)->except(['destroy'])->parameters(['packages' => 'package']);
        Route::delete('packages/{package}', [PackageController::class, 'destroy']);
        Route::post('packages/{package}/publish', [PackageController::class, 'publish']);
        Route::post('packages/{package}/revert-to-draft', [PackageController::class, 'revertToDraft']);
        Route::post('packages/{package}/archive', [PackageController::class, 'archive']);
        Route::post('packages/{package}/restore', [PackageController::class, 'restore']);

        Route::apiResource('add-ons', AddOnController::class)->parameters(['add-ons' => 'addOn']);
        Route::post('add-ons/{addOn}/archive', [AddOnController::class, 'archive']);
        Route::post('add-ons/{addOn}/restore', [AddOnController::class, 'restore']);

        Route::get('custom-package/config', [CustomPackageController::class, 'showConfig']);
        Route::patch('custom-package/config', [CustomPackageController::class, 'updateConfig']);
        Route::get('custom-package/components', [CustomPackageController::class, 'indexComponents']);
        Route::post('custom-package/components', [CustomPackageController::class, 'storeComponent']);
        Route::patch('custom-package/components/{component}', [CustomPackageController::class, 'updateComponent']);
        Route::post('custom-package/components/{component}/archive', [CustomPackageController::class, 'archiveComponent']);
        Route::post('custom-package/components/{component}/restore', [CustomPackageController::class, 'restoreComponent']);
    });
});
Route::get('photographers/{user}/availability/calendar', [PublicPhotographerAvailabilityController::class, 'calendar']);
Route::get('photographers/{user}/availability/slots', [PublicPhotographerAvailabilityController::class, 'slots']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('photographer')->group(function () {
        Route::get('availability-windows', [AvailabilityWindowController::class, 'index']);
        Route::post('availability-windows', [AvailabilityWindowController::class, 'store']);
        Route::patch('availability-windows/{availabilityWindow}', [AvailabilityWindowController::class, 'update']);
        Route::delete('availability-windows/{availabilityWindow}', [AvailabilityWindowController::class, 'destroy']);

        Route::get('blocked-dates', [BlockedDateController::class, 'index']);
        Route::post('blocked-dates', [BlockedDateController::class, 'store']);
        Route::patch('blocked-dates/{blockedDate}', [BlockedDateController::class, 'update']);
        Route::delete('blocked-dates/{blockedDate}', [BlockedDateController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('client')->group(function () {
        Route::get('bookings', [ClientBookingController::class, 'index']);
        Route::post('bookings', [ClientBookingController::class, 'store']);
        Route::get('bookings/{booking}', [ClientBookingController::class, 'show']);
        Route::post('bookings/{booking}/request-cancellation', [ClientBookingController::class, 'requestCancellation']);

        Route::get('bookings/{booking}/payment-info', [ClientPaymentController::class, 'paymentInfo']);
        Route::post('bookings/{booking}/payments', [ClientPaymentController::class, 'store']);
        Route::get('payments', [ClientPaymentController::class, 'index']);
    });

    Route::prefix('photographer')->group(function () {
        Route::get('bookings', [PhotographerBookingController::class, 'index']);
        Route::get('bookings/{booking}', [PhotographerBookingController::class, 'show']);
        Route::post('bookings/{booking}/accept', [PhotographerBookingController::class, 'accept']);
        Route::post('bookings/{booking}/reject', [PhotographerBookingController::class, 'reject']);
        Route::post('bookings/{booking}/cancellation/approve', [PhotographerBookingController::class, 'approveCancellation']);
        Route::post('bookings/{booking}/cancellation/reject', [PhotographerBookingController::class, 'rejectCancellation']);

        Route::get('payment-config', [PaymentConfigController::class, 'show']);
        Route::post('payment-config', [PaymentConfigController::class, 'store']);
        Route::post('bookings/{booking}/payments/onsite', [PhotographerPaymentController::class, 'storeOnsite']);
        Route::get('payments', [PhotographerPaymentController::class, 'index']);

        Route::get('payment-references', [PaymentReferenceController::class, 'index']);
        Route::post('payment-references', [PaymentReferenceController::class, 'store']);
        Route::post('payment-references/{paymentReference}/invalidate', [PaymentReferenceController::class, 'invalidate']);

        Route::post('payments/{payment}/verify', [PhotographerPaymentController::class, 'verify']);
        Route::post('payments/{payment}/reject', [PhotographerPaymentController::class, 'reject']);
    });

    Route::get('admin/payments', [AdminPaymentController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});