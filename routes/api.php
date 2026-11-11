<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GPM Conecta API
|--------------------------------------------------------------------------
|
| Public and authenticated API endpoints will be grouped by version.
| Business rules must remain in Actions/Services, not in route closures.
|
*/

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', static fn () => response()->json([
            'application' => config('app.name'),
            'status' => 'ok',
            'api_version' => 'v1',
        ]))->name('health');
    });
