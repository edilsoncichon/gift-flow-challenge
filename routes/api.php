<?php

use App\Http\Controllers\ProcessWebhookController;
use App\Http\Controllers\RedeemGiftCardController;
use App\Http\Middleware\ValidateWebhookSignatureMiddleware;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function () {
    Route::post(
        'redeem',
        RedeemGiftCardController::class
    );
});

Route::group([
    'middleware' => [ValidateWebhookSignatureMiddleware::class],
    'prefix' => 'webhook',
], function () {
    Route::post(
        'issuer-platform',
        ProcessWebhookController::class
    );
});
