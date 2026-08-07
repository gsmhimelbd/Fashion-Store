<?php

use App\Http\Controllers\Api\VCardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', [VCardController::class, 'user']);
    Route::get('/cards', [VCardController::class,'index']);
    Route::get('/cards/{vcard}', [VCardController::class,'show']);
    Route::patch('/cards/{vcard}', [VCardController::class,'update']);
});
