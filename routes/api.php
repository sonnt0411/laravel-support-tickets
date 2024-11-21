<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\TicketController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [ApiAuthController::class, 'login'])->name('api.login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('categories', CategoryController::class)->middleware('role:admin');
    Route::apiResource('labels', LabelController::class)->middleware('role:admin');

    Route::get('categories', [CategoryController::class, 'index'])->middleware('role:user,agent');
    Route::get('labels', [LabelController::class, 'index'])->middleware('role:user,agent');

    // Route index được truy cập bởi tất cả các role
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

    // Route store chỉ được truy cập bởi role 'user'
    Route::post('/tickets', [TicketController::class, 'store'])
        ->middleware('role:user')
        ->name('tickets.store');
});