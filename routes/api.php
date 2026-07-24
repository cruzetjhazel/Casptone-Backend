<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\PhotographerApplicationController as AdminPhotographerApplicationController;
use App\Http\Controllers\Api\PhotographerApplicationController;
use App\Http\Controllers\Api\Admin\PhotographerPortfolioController as AdminPhotographerPortfolioController;
use App\Http\Controllers\Api\Photographer\PortfolioController;
use App\Http\Controllers\Api\Photographer\ProfileController;
use App\Http\Controllers\Api\PublicPhotographerController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
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