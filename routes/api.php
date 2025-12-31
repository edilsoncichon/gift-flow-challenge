<?php

use App\Http\Controllers\RedeemGiftCardController;
use Illuminate\Support\Facades\Route;

Route::post('redeem', RedeemGiftCardController::class);
