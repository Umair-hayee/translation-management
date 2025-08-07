<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\AuthController;

// Route::get('/ping', function () {
//     return response()->json(['message' => 'pong']);
// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::prefix('translations')->middleware('auth:api')->group(function () {
    Route::get('/', [TranslationController::class, 'index']);
    Route::post('/', [TranslationController::class, 'store']);
    Route::get('/{translation}', [TranslationController::class, 'show']);
    Route::put('/{translation}', [TranslationController::class, 'update']);
});

Route::get('/translations/export', [TranslationController::class, 'export']);