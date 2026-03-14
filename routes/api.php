<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// WhatsApp incoming webhook (public — auth via token header dari Fonnte)
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle'])
    ->name('api.whatsapp.webhook');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
    Route::get('/leave-balance', [LeaveRequestController::class, 'balance']);
});
