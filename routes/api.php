<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ItineraireController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/itineraires',[ItineraireController::class,'index']);

Route::middleware(['jwt'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::get('/my-itineraires', [ItineraireController::class,'itinerairesByUser']);
    Route::post('/categorie/add',[CategorieController::class,'create']);
    Route::get('/categories',[CategorieController::class,'index']);
    Route::post('/itineraires/add',[ItineraireController::class,'create']);
    Route::get('/itineraires/{id}/visiter',[ItineraireController::class,'visiter']);
});
