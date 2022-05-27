<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\KeywordSetController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SplogController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// auth routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(RedirectIfAuthenticated::class)
    ->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(RedirectIfAuthenticated::class);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(Authenticate::class)
    ->name('logout');

// app routes
Route::middleware(['auth'])->group(static function() {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)
        ->except(['show', 'update']);
    Route::resource('keyword-sets', KeywordSetController::class)
        ->parameters(['keyword-sets' => 'set'])
        ->except(['show', 'update']);
    Route::delete('/keyword-sets/{set}/markov', [KeywordSetController::class, 'destroyMarkov'])->name('keyword-sets.destroy-markov');
    Route::delete('/keyword-sets/{set}/cache', [KeywordSetController::class, 'destroyArticles'])->name('keyword-sets.destroy-articles');
    Route::resource('keyword-sets.keywords', KeywordController::class)
        ->except(['show', 'update'])
        ->shallow();
    Route::delete('/keywords/{keyword}/markov', [KeywordController::class, 'destroyMarkovCache'])->name('keywords.destroy-markov');
    Route::delete('/keywords/{keyword}/articles', [KeywordController::class, 'destroyArticlesCache'])->name('keywords.destroy-articles');
    Route::get('/keywords/{keyword}/markov/regenerate', [KeywordController::class, 'regenerateMarkov'])->name('keywords.regenerate-markov');
    Route::get('/keywords/{keyword}/cache/regenerate', [KeywordController::class, 'fetchArticlesRegenerateMarkov'])->name('keywords.regenerate-cache');
    Route::delete('/projects/{project}/splog/{splog}', [ProjectController::class, 'destroySplog'])->name('projects.destroySplog');
    Route::resource('projects', ProjectController::class)
        ->except(['update']);
    Route::resource('servers', ServerController::class);
    Route::get('/servers/{id}/firewall-required-rules', [ServerController::class, 'addRequiredFirewallRules'])->name('servers.firewall-required-rules');
    Route::resource('splogs', SplogController::class);
    Route::resource('settings', SettingsController::class)
        ->only(['index', 'store', 'edit']);
});

