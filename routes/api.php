<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ColoresController;
use App\Http\Controllers\Api\MarcasController;
use App\Http\Controllers\Api\ModelosController;
use App\Http\Controllers\Api\ArticulosController;
use App\Http\Controllers\Api\SolicitudController;
use App\Http\Controllers\Api\NomenclaturasController;
use App\Http\Controllers\Api\DespachoController;
use App\Http\Controllers\Api\SalidaController;

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
    //Solicitudes
    Route::apiResource('solicitud_salida', SalidaController::class);
    Route::apiResource('solicitud', SolicitudController::class);
    Route::apiResource('despachos', DespachoController::class);
    Route::get('/solicitudes/{id}', [SolicitudController::class,'show']);
    Route::delete('/eliminar_articulo/{id}', [SolicitudController::class,'destroy']);
    Route::post('/agregar_articulo_detalle/{id}/solicitud',[SolicitudController::class,'agregarArticulo']);
    Route::post('/editar_articulo_detalle/{id}/detalle', [SolicitudController::class,'editarArticulo']);
    Route::post('/editar_solicitud/{id}/solicitud', [SolicitudController::class,'editarSolicitud']);
    Route::delete('/eliminar_solicitud/{id}/solicitud', [SolicitudController::class,'eliminarSolicitud']);
    Route::post('/confirmar_solicitud/{id}/confirmar', [SolicitudController::class,'confirmarSolicitud']);
    Route::get('/articulos_stock', [ArticulosController::class,'articulosStock']);
    Route::get('nomenclaturas_por_despacho/{fk_despacho}/nomenclaturas',[NomenclaturasController::class,'PorDespacho']);
    Route::get('articulos_por_nomenclaturas_en_stock/{fk_modelo}/articulos',[NomenclaturasController::class,'ArticulosModelos']);
    Route::post('editar_encabezado_salida/{id}/editar', [SalidaController::class,'editarSalidaEncabezado']);



});
