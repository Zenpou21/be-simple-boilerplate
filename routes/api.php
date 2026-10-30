<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Every route here starts with /api, e.g. 'products/list' is http://localhost:8000/api/products/list.
| See them all with:  php artisan route:list --path=api
|
| To add a module: make a controller (copy ProductController), then add a group like the
| products one below.
|
*/

// Open to everyone. throttle:10,1 = at most 10 tries per minute (slows down password guessing).
Route::post('auth/login', [AuthController::class, 'onLogin'])->middleware('throttle:10,1');

// Signed-in users only: 'auth:api' answers 401 when the token is missing or expired.
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'onGetUser']);
    Route::post('logout', [AuthController::class, 'onLogout']);

    Route::prefix('products')->controller(ProductController::class)->group(function () {
        Route::get('list', 'onGetList'); // before {product}, or "list" would be read as an id
        Route::get('{product}', 'onGetOne');
        Route::post('/', 'onCreate');
        Route::put('{product}', 'onUpdate');
        Route::delete('{product}', 'onDelete');
    });
});
