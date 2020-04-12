<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

Route::prefix('home')->middleware('auth:api')->group(function(){
    Route::any('/test','HomeControllerApi@test');
});

Route::middleware('auth:api')->any('/deals_log','ApiDealsLogsController@getDealsLog');
Route::middleware('auth:api')->any('/upload_csv','ApiDealsLogsController@uploadCsv');
Route::middleware('auth:api')->any('/random_deal_logs','ApiDealsLogsController@generateRandomDealLogs');
Route::middleware('auth:api')->any('/empty_deal_logs','ApiDealsLogsController@emptyDealLogsTable');
