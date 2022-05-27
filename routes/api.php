<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/dashboard/jobs', [DashboardController::class, 'jobs'])->name('api/dashboard/jobs');
Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('api/dashboard/stats');
Route::get('/dashboard/cache', [DashboardController::class, 'cache'])->name('api/dashboard/cache');
Route::get('/servers/{id}/firewall', [ServerController::class, 'firewallStatus'])->name('api/servers/firewall-status');
