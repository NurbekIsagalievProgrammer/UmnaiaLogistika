<?php

use App\Http\Controllers\Api\BulkNotificationController;
use App\Http\Controllers\Api\SubscriberNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('notifications/bulk', [BulkNotificationController::class, 'store']);
    Route::get('subscribers/{subscriberId}/notifications', [SubscriberNotificationController::class, 'index']);
});
