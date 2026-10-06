<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nomenclatura;
use Illuminate\Http\Request;
use App\Models\Despacho;
use App\Models\VistaArticulos;

class NomenclaturasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function PorDespacho($id_despacho)
    {
        try {
            $despacho = Despacho::with('nomenclaturas')
            ->where('id_despacho', $id_despacho)->first();

            if (!$despacho) {
                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'El despacho no existe'
                ],404);
            }

            return response()->json([
                'ok'=>true,
                'data'=>$despacho
            ],200);
        } catch (\Exception $th) {
            return response()->json([
                'ok'=>false,
                'error'=>'No se pudo consultar las nomenclaturas',
                'data'=>$th->getMessage()
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function ArticulosModelos($fk_modelo)
    {
        try {
            $articulos = VistaArticulos::
            select
            (
                'id_articulo','codigo','categoria','color','marca','modelo','modelo_insumo','detalle','stock','fk_modelo'
            )->where('fk_modelo', $fk_modelo)->where('stock','>', 0)->get();

            if (!$articulos) {
                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'Esta nomenclatura tiene artículo en stock'
                ],404);
            }

            return response()->json([
                'ok'=>true,
                'data'=>$articulos
            ],200);
        } catch (\Exception $th) {
            return response()->json([
                'ok'=>false,
                'mensaje'=>'No se pudo mostrar artículos en está nomenclaturas'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Nomenclatura $nomenclatura)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nomenclatura $nomenclatura)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nomenclatura $nomenclatura)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nomenclatura $nomenclatura)
    {
        //
    }
}
