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
////user route
Route::post('register',[\App\Http\Controllers\AuthController::class,'register']);
Route::post('register',[\App\Http\Controllers\AuthController::class,'login']);
Route::group(['middleware'=>['auth:sanctum']],function ()
{
    Route::post('/logout',[\App\Http\Controllers\AuthController::class,'logout']);
    Route::apiResource('brands',\App\Http\Controllers\BrandController::class);
    Route::get('brands/{brand}/products',[\App\Http\Controllers\BrandController::class,'products']);
///category route
    Route::apiResource('categories',\App\Http\Controllers\CategoryController::class);
    Route::get('categories/{category}/sub_category',[\App\Http\Controllers\CategoryController::class,'sub_category']);
    Route::get('categories/{category}/parent_category',[\App\Http\Controllers\CategoryController::class,'parent_category']);
    Route::get('categories/{category}/products',[\App\Http\Controllers\CategoryController::class,'products']);
///product route
    Route::apiResource('products',\App\Http\Controllers\ProductController::class);
///payment route

});
///////brand route

Route::post('/payment/send',[\App\Http\Controllers\PaymentController::class, 'send']);
Route::post('/payment/verify',[\App\Http\Controllers\PaymentController::class, 'verify']);


