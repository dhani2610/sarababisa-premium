<?php

use App\Http\Controllers\Api\AkunController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('customers', 'Api\Customers@index')->name('api-customers');

Route::get('/get-total-cabang', [AkunController::class, 'getDataTotalCabang'])->name('get-total-cabang');
Route::post('/update-total-cabang', [AkunController::class, 'updateTotalCabang'])->name('update-total-cabang');
Route::get('/get-setting-cabang', [AkunController::class, 'getCabangWithExpiredDate'])->name('get-setting-cabang');
Route::post('/update-expired-date-cabang', [AkunController::class, 'updateExpiredDateCabang'])->name('update-expired-date-cabang');

