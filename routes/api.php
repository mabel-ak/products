<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

    Route::any('/', function () {
        return response()->json(['message' => 'Welcome to Products Api'], 200);
    })->name('welcome');

    // Declare unauthenticated routes
    Route::group(['middleware' => 'guest', 'prefix'=>'v1'], function ($router) {

        Route::post('register', [AuthController::class, 'register'])->name('register');

        Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('otp/login', [AuthController::class, 'otp/login'])->name('otp/login');

        Route::get('/email/verify', function () {
            return view('auth.verify-email');
        })->middleware([AuthController::class, '/email/verify'])->name('verification.notice');

 
        Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
 
    return redirect('/home');
        })->middleware(['auth', 'signed'])->name('verification.verify');
 
        Route::post('/email/verification-notification', function (Request $request) {
            $request->user()->sendEmailVerificationNotification();
         
            return back()->with('message', 'Verification link sent!');
        })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
