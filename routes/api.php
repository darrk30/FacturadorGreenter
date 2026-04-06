<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\VoidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', [RegisterController::class, 'store']);
Route::middleware('auth:api')->group(function () {
    Route::apiResource('companies', CompanyController::class);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('me', [AuthController::class, 'me']);

Route::middleware('auth.apikey')->group(function () {
    Route::post('invoices/send', [InvoiceController::class, 'send']);
    Route::post('invoice/send-xml', [InvoiceController::class, 'sendXml']);
    Route::post('invoices/xml', [InvoiceController::class, 'xml']);
    Route::post('invoices/pdf', [InvoiceController::class, 'pdf']);
    Route::post('summaries/send', [SummaryController::class, 'send']);
    Route::post('summaries/status', [SummaryController::class, 'status']);
    Route::post('voids/send', [VoidController::class, 'send']);
    Route::post('voids/status', [VoidController::class, 'status']);
});
