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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/loans', 'LoanController@index');
Route::post('/loans', 'LoanController@store');
Route::post('/loans/{loan}/full/{terms?}','LoanController@fullRepayment');
Route::post('/loans/{loan}/pay','LoanController@repayment');
Route::patch('/loans/{loan}', 'LoanController@update');
Route::patch('/loans/{loan}/approve', 'LoanController@approve');
