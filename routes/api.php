<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CommissionSummaryController;
use App\Http\Controllers\Api\ImportTransactionsController;
use App\Http\Controllers\Api\ListTransactionsController;
use Illuminate\Support\Facades\Route;

Route::post('imports', ImportTransactionsController::class);
Route::get('transactions', ListTransactionsController::class);
Route::get('commissions/summary', CommissionSummaryController::class);
