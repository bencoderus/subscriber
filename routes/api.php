<?php

use App\Http\Controllers\TopicController;
use App\Models\Topic;
use Illuminate\Support\Facades\Http;
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

Route::get('/', fn () => response()->json(['status' => true, 'message' => 'Welcome to the notification system.'], 200));

Route::post('/subscribe/{topic:slug}', [TopicController::class, 'subscribe'])->name('subscribe');
Route::post('/publish/{topic:slug}', [TopicController::class, 'publish'])->name('publish');
