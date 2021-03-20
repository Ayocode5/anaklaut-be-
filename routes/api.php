<?php

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {

    Route::post('login', [App\Http\Controllers\API\AuthControllerAPI::class, 'login']);
    Route::post('logout', [App\Http\Controllers\API\AuthControllerAPI::class, 'logout']);
    Route::post('register', [App\Http\Controllers\API\AuthControllerAPI::class, 'register']);
    Route::post('refresh', [App\Http\Controllers\API\AuthControllerAPI::class, 'refresh']);
    Route::get('me', [App\Http\Controllers\API\AuthControllerAPI::class, 'me']);
});

Route::group(["prefix" => "v1"], function () {
    
    Route::get('/products', [\App\Http\Controllers\API\ProductController::class, "all"]);

    Route::post('/transaction/token', [\App\Http\Controllers\API\TransactionController::class, 'token'])->name('api.payment.token');
    Route::post('/transaction/finish', [\App\Http\Controllers\API\TransactionController::class, 'finish'])->name('api.payment.finish');
    Route::post('/transaction/notification', [\App\Http\Controllers\API\TransactionController::class, 'notification'])->name('api.payment.notification');
    Route::get('/transaction/{orderId}/status', [\App\Http\Controllers\API\TransactionController::class, 'status']);
});
