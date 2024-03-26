<?php

use  Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

Route::get('/', function () {
    return view('welcome');
});


///payment route
///Route::get('/payment/verify',function (Request $request)
///{
///   $response=Http::post('https://localhost:8000/api/payment/verify',
///       [
///           'trackId'=>$request->trackId,
///           'success'=>$request->success,
///       ]);
///    dd($response->json());
///});
