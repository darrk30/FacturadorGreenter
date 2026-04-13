<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\VoidController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('me', [AuthController::class, 'me']);

Route::middleware('auth:api')->group(function () {
    // Registrar Usuario
    Route::post('register', [RegisterController::class, 'store']);

    // Rutas para empresas
    Route::apiResource('companies', CompanyController::class);
});

Route::middleware('auth.apikey')->group(function () {
    Route::post('my-company/update', [CompanyController::class, 'updateViaApi']); // Ruta para actualizar la empresa vía API
    Route::get('companies/{company}/certificate', [CompanyController::class, 'downloadCertificate']); // Ruta para descargar el certificado de la empresa

    // Rutas para facturas
    Route::post('invoices/send', [InvoiceController::class, 'send']);
    Route::post('invoice/send-xml', [InvoiceController::class, 'sendXml']);
    Route::post('invoices/xml', [InvoiceController::class, 'xml']);
    Route::post('invoices/pdf', [InvoiceController::class, 'pdf']);

    // Rutas para resúmenes diarios
    Route::post('summaries/send', [SummaryController::class, 'send']);
    Route::post('summaries/status', [SummaryController::class, 'status']);

    // Rutas para anulaciones
    Route::post('voids/send', [VoidController::class, 'send']);
    Route::post('voids/status', [VoidController::class, 'status']);

    // Rutas para notas de crédito y débito
    Route::post('notes/send', [NoteController::class, 'send']);
    Route::post('notes/xml', [NoteController::class, 'xml']);
    Route::post('notes/pdf', [NoteController::class, 'pdf']);
});
