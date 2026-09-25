<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articulos;
use Illuminate\Http\Request;
use App\Http\Requests\Articulos\RequestRegistrar;
use Illuminate\Support\Facades\DB;

class ArticulosController extends Controller
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequestRegistrar $request)
    {
        try {
            DB::beginTransaction();

            $datos = $request->validated();

            $consulta = Articulos::where('codigo', strtoupper(trim($datos['codigo'])))->count();
            if ($consulta > 0) {

                DB::rollback();

                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'Existe un artículo con este código'
                ],409);
            }

            //Usuario logueado
            $usuario = $request->user();

            if (!$usuario) {
                DB::rollback();

               return response()->json([
                'ok'=>false,
                'mensaje'=>'El usuario no está autenticado'
               ],401);
            }

            //registrar articulo
            $articulo = Articulos::create([
                'codigo'=> strtoupper(trim($datos['codigo'])),
                'fk_marca'=>$datos['fk_marca'],
                'fk_categoria'=>$datos['fk_categoria'],
                'fk_modelo'=>$datos['fk_modelo'],
                'modelo_insumo'=>strtoupper(trim($datos['modelo_insumo'])),
                'fk_color'=>$datos['fk_color'] ?? null,
                'detalle'=>$datos['detalle'] ?? null,
                'usuario_crea'=>strtoupper($usuario->usuario),
            ]);

            DB::commit();

            return response()->json([
                'ok'=>true,
                'data'=>$articulo,
                'mensaje'=>'El artículo se registro correctamente'
            ],201);
        } catch (\Exception $th) {

            DB::rollback();

            return response()->json([
                'ok'=>false,
                'mensaje'=>'No se pudo registrar el artículo',
                'error'=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Articulos $articulos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Articulos $articulos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Articulos $articulos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Articulos $articulos)
    {
        //
    }
}
