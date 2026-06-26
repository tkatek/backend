<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\OcrController;

// Restaurant Profiling & Dashboard Actions
Route::post('/restaurants', [RestaurantController::class, 'store']);

// Public Consumer Live View (Eager loaded for < 2-second render speeds)
Route::get('/public-menu/{slug}', [RestaurantController::class, 'show']);

// OCR Content Factory Processing Endpoint
Route::post('/restaurants/{restaurant}/import-ocr', [OcrController::class, 'parseAndSave']);