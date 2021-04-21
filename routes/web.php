<?php

use App\Http\Controllers\PaymentController;
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

Route::get('/',[PaymentController::class,'demoPayment']);
Route::get('/payment/demo-pay',[PaymentController::class,'demoPay']);
Route::get('/payment/success',[PaymentController::class,'successPayment']);
Route::get('/payment/cancel',[PaymentController::class,'cancelPayment']);
