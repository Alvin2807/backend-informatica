<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ColoresController;
use App\Http\Controllers\Api\MarcasController;
use App\Http\Controllers\Api\ModelosController;
use App\Http\Controllers\Api\ArticulosController;
Route::post('/registrar_usuario', [AuthController::class, 'registrar']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Categorías
    Route::apiResource('categorias', CategoriaController::class);
    //Colores
    Route::apiResource('colores', ColoresController::class);
    //Marcas
    Route::apiResource('marcas', MarcasController::class);
    Route::post('editar_marca', [MarcasController::class, 'editar']);
    //Modelos
    Route::apiResource('modelos', ModelosController::class);
    //Articulos
    Route::apiResource('articulos', ArticulosController::class);


});
