<?php

use App\Http\Controllers\ApiSimulatorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/public-data', function () {
    return response()->json(['message' => 'Esta es una API pública']);
});

Route::post('/simulate-post', [ApiSimulatorController::class, 'sendPost']);

Route::get('/debug-external-ip', function () {
    // Consulta externa que devuelve la IP pública de origen
    $response = Http::get('https://httpbin.org/ip');
    return $response->json();
});
