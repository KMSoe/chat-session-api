<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\App\Http\Controllers\ChatSessionController;

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

Route::prefix('v1')->group(function () {
    Route::prefix('chat-sessions')->name('chat-sessions.')->group(function () {
        Route::get('', [ChatSessionController::class, 'index'])->name('index');
        Route::get('/{session_uuid}', [ChatSessionController::class, 'show'])->name('show');
        Route::post('', [ChatSessionController::class, 'store'])->name('store');
        Route::put('/{session_uuid}', [ChatSessionController::class, 'update'])->name('update');
        Route::delete('/{session_uuid}', [ChatSessionController::class, 'destroy'])->name('destroy');
    });
    Route::delete('chat-sessions-bulk-delete', [ChatSessionController::class, 'bulkDelete'])->name('chat-sessions.bulkDelete');
});
