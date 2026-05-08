<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'OneHealth API',
        'docs' => '/api/v1/onehealth/auth/login (POST JSON: email, password)',
        'health' => '/up',
    ]);
});
