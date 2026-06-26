<?php

use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\MenuController;
use Illuminate\Support\Facades\Route;

Route::apiResource('restaurants', RestaurantController::class);
Route::get('public-menu/{slug}', [MenuController::class, 'getPublicMenu']);