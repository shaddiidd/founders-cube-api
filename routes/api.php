<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Models\User;

// Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::get('/get-referrals', [ApplicationController::class, 'referrals']);
Route::post('/apply', [ApplicationController::class, 'store']);
Route::put('/password', [UserAuthController::class, 'setPassword']);

Route::post('/forgot-password', [UserAuthController::class, 'forgotPasswordRequest']);
Route::post('/reset-password', [UserAuthController::class, 'resetPassword']);

Route::middleware(['auth:api', 'user_type:admin'])->group(function () {
    Route::get('/admin-members', [UserAuthController::class, 'adminIndex']);

    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/home-applications', [ApplicationController::class, 'homeIndex']);
    Route::get('/applications/{id}', [ApplicationController::class, 'show']);
    Route::put('/confirm-application/{id}', [ApplicationController::class, 'confirm']);
    Route::put('/decline-application/{id}', [ApplicationController::class, 'decline']);
    Route::get('/special-members', [UserAuthController::class, 'indexSpecialMembers']);
    Route::post('/special-members', [UserAuthController::class, 'storeSpecialMembers']);
    Route::put('/special-members/{id}', [UserAuthController::class, 'updateSpecialMember']);
    Route::post('/special-member/profile-picture/{id}', [UserAuthController::class, 'specialMemberProfilePicture']);
    Route::delete('/special-members/{id}', [UserAuthController::class, 'deleteSpecialMember']);

    Route::put('/verify/{id}', [UserAuthController::class, 'verify']);
    Route::put('/unverify/{id}', [UserAuthController::class, 'unverify']);

    Route::post('/packages', [PackageController::class, 'store']);
    Route::put('/packages/{id}', [PackageController::class, 'update']);
    Route::delete('/packages/{id}', [PackageController::class, 'destroy']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/home-transactions', [TransactionController::class, 'homeIndex']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::put('/confirm-transaction/{id}', [TransactionController::class, 'confirm']);
    Route::put('/decline-transaction/{id}', [TransactionController::class, 'decline']);

    Route::get('/dashboard/graph/applications', [DashboardController::class, 'graph_applications']);
    Route::get('/dashboard/graph/transactions', [DashboardController::class, 'graph_transactions']);

    Route::put('/request-payment/{id}', [TransactionController::class, 'deactivateAccountForPayment']);
    Route::put('/free-months/{id}', [TransactionController::class, 'freeMonths']);
    Route::delete('/kickout/{id}', [UserAuthController::class, 'kickout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if ($user->transactions()->latest()->first() != null) {
            $latestTransaction = $user->transactions()->latest()->first();
            $user->latest_transaction = $latestTransaction->created_at;
            $user->latest_transaction_status = $latestTransaction->status;
        }
        return $user;
    });
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::put('/user', [UserAuthController::class, 'update']);
    Route::post('/profile-picture', [UserAuthController::class, 'profilePicture']);
    Route::put('/change-password', [UserAuthController::class, 'changePassword']);
    Route::get('/special-members', [UserAuthController::class, 'specialMembers']);
    Route::get('/members', [UserAuthController::class, 'index']);
    Route::get('/members/{id}', [UserAuthController::class, 'show']);
    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/packages/{id}', [PackageController::class, 'show']);

    Route::post('/subscribe/{id}', [TransactionController::class, 'store']);

    Route::get('/configs', [ConfigController::class, 'index']);
    Route::put('/update-configs', [ConfigController::class, 'updateAll']);
    Route::put('/configs/{key}', [ConfigController::class, 'update']);
});