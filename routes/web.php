<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TikTokController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [TikTokController::class, 'index'])->name('home');
Route::post('/search', [TikTokController::class, 'search'])->name('search');
Route::get('/user/{username}', [TikTokController::class, 'userProfile'])->name('user.profile');
Route::post('/load-more', [TikTokController::class, 'loadMorePosts'])->name('load.more'); 