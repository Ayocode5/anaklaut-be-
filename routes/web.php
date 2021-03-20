<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('user/logout', [App\Http\Controllers\Auth\LoginController::class, 'logoutUser'])->name('user.logout');

Route::group(['prefix' => 'admin'], function(){
    // Admin Authentication
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/login', [App\Http\Controllers\AuthAdmin\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [App\Http\Controllers\AuthAdmin\LoginController::class, 'login'])->name('admin.login.submit');
    Route::get('/logout', [App\Http\Controllers\AuthAdmin\LoginController::class, 'logoutAdmin'])->name('admin.logout');
    Route::post('/password/email', [App\Http\Controllers\AuthAdmin\ForgotPasswordController::class, 'sendResetLinkAccount'])->name('admin.password.email');
    Route::get('/password/reset', [App\Http\Controllers\AuthAdmin\ForgotPasswordController::class, 'showPasswordResetForm'])->name('admin.password.request');
    Route::get('/password/reset/{token}/{email}', [App\Http\Controllers\AuthAdmin\ResetPasswordController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('/password/reset', [App\Http\Controllers\AuthAdmin\ResetPasswordController::class, 'reset'])->name('admin.password.update');
    Route::get('/register', [App\Http\Controllers\AuthAdmin\RegisterController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/register', [App\Http\Controllers\AuthAdmin\RegisterController::class, 'register']);
    Route::get('/get-dashboard-info', [\App\Http\Controllers\AdminController::class, 'getDashboardInfo'])->name('get.dashboard.info');

    // Product
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index']);
    Route::get('/products/add', [App\Http\Controllers\ProductController::class, 'create']);
    Route::post('/products/add', [App\Http\Controllers\ProductController::class, 'store'])->name('product.new');
    Route::get('/products/{id}/edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('product.edit');
    Route::patch('/products/update/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('product.update');
    Route::get('/products/detail/{id}', [App\Http\Controllers\ProductController::class, 'show'])->name('product.detail');
    Route::get('/products/destroy/{id}', [App\Http\Controllers\ProductController::class, 'destroy'])->name('product.destroy');

    // Order
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index']);
    
    // Midtrans Gateway Test
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index']);
    Route::get('/transaction/{orderId}/details', [\App\Http\Controllers\TransactionController::class, 'status'])->name("status.transaction");
    Route::get('/transaction/{orderId}/approve', [\App\Http\Controllers\TransactionController::class, 'approve'])->name("approve.transaction");
    Route::post('/transaction/{orderId}/refund', [\App\Http\Controllers\TransactionController::class, 'refund'])->name("refund.transaction");
});

Route::get('/payment', [\App\Http\Controllers\TransactionController::class, 'checkout'])->name('payment');
Route::post('/payment/token', [\App\Http\Controllers\TransactionController::class, 'token'])->name('payment.token');
Route::post('/payment/finish', [\App\Http\Controllers\TransactionController::class, 'finish'])->name('payment.finish');
Route::post('/payment/notification', [\App\Http\Controllers\TransactionController::class, 'notification']);