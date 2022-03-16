<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/unauthenticated', [ApiAuthController::class, 'unauthenticated'])->name('unauthenticated');

    Route::post('/forget-password', [ApiAuthController::class, 'submitForgetPasswordForm']);
    Route::post('/reset-password', [ApiAuthController::class, 'submitResetPasswordForm']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/private-area', [ApiAuthController::class, 'private_area']);
    });
});
