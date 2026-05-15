<?php
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizatorController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicSectionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:6,1');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed:relative', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/sections', [PublicSectionController::class, 'index']);
Route::get('/sections/{slug}', [PublicSectionController::class, 'show']);
Route::get('/sections/{slug}/reviews', [PublicSectionController::class, 'getReviews']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/name', [ProfileController::class, 'updateName']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/user/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/email', [ProfileController::class, 'updateEmail']);
    Route::post('/become-organizator', [ProfileController::class, 'becomeOrganizator']);
    Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/delete', [ProfileController::class, 'destroy']);

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:1,1');

    Route::get('/admin', function (Request $request) {
        return response()->json([
            'message' => 'Admin Panel',
            'user' => $request->user(),
        ]);
    })->middleware('admin');

    Route::get('/admin/users', [AdminController::class, 'index'])->middleware('admin');
    Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateRole'])->middleware('admin');
    Route::put('/admin/users/{user}/status', [AdminController::class, 'updateStatus'])->middleware('admin');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->middleware('admin');

    Route::get('/organizator', [OrganizatorController::class, 'index'])->middleware('organizator');
    Route::post('/organizator/profile', [OrganizatorController::class, 'updateProfile'])->middleware('organizator');
    Route::post('/organizator/sections', [OrganizatorController::class, 'storeSection'])->middleware('organizator');
    Route::post('/organizator/sections/{section}', [OrganizatorController::class, 'updateSection'])->middleware('organizator');
    Route::delete('/organizator/sections/{section}', [OrganizatorController::class, 'destroySection'])->middleware('organizator');
    Route::post('/organizator/sections/{section}/schedules', [OrganizatorController::class, 'storeSchedule'])->middleware('organizator');
    Route::delete('/organizator/schedules/{schedule}', [OrganizatorController::class, 'destroySchedule'])->middleware('organizator');
    Route::patch('/organizator/applications/{application}', [OrganizatorController::class, 'updateApplicationStatus'])->middleware('organizator');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::post('/sections/{slug}/applications', [PublicSectionController::class, 'apply']);
    Route::post('/pieraksti', function (Request $request) {
        $slug = (string) $request->validate([
            'section_slug' => ['required', 'string'],
        ])['section_slug'];

        return app(PublicSectionController::class)->apply($request, $slug);
    });
    Route::post('/sections/{slug}/reviews', [PublicSectionController::class, 'addReview']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
