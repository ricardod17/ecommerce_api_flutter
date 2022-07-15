<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductGalleryController;
use App\Helpers\ResponseFormatter;
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


Route::middleware(['middleware' => 'auth:api'])->group(function () {
    Route::post('checkout', [TransactionController::class, 'checkout']);
    Route::get('user', [UserController::class, 'fetch']);
    Route::get('user/{id}', [UserController::class, 'show']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('categories', [ProductCategoryController::class, 'index']);
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('users', [UserController::class, 'index']);
});


Route::prefix('v2')->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::post('login', [UserController::class, 'postLogin']);
    Route::post('register', [UserController::class, 'postRegister']);
});

Route::group([
    'prefix' => 'v2',
    'middleware' => 'auth:api',
], function () {
    Route::patch('transaction/{id}', [TransactionController::class, 'update', 'index']);
    Route::resource('products', ProductController::class)->except(['index']);
    Route::resource('categories', ProductCategoryController::class);
    Route::resource('galleries', ProductGalleryController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('users', UserController::class);
});


Route::fallback(function () {
    return ResponseFormatter::error('no Route matched with those values', 404);
});