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

    # Ticket routes group
    Route::prefix('tickets')->group(function () {
        Route::post('/', [TicketController::class, 'store'])->middleware('role:user');
        Route::get('/', [TicketController::class, 'index']);
        // Route::get('/{ticket}', [TicketController::class, 'show'])->middleware('role:user,agent');
        // Route::put('/{ticket}', [TicketController::class, 'update'])->middleware('role:agent');
        // Route::delete('/{ticket}', [TicketController::class, 'destroy'])->middleware('role:admin');
    });
});