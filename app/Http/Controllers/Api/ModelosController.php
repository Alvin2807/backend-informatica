<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modelos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VistaModelos;
class ModelosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modelo = VistaModelos::all();
        return response()->json([
          'ok' =>true,
          'data' =>$modelo
        ]);
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
    public function store(Request $request)
{
    DB::beginTransaction();

    try {

        $datos = $request->validate([
            'fk_marca' => 'required|integer|exists:inv_marcas,id_marca',
            'modelo' => 'required|string|max:150',
        ]);

        $modeloNombre = strtoupper(trim($datos['modelo']));

        $count = Modelos::where('fk_marca', $datos['fk_marca'])
            ->where('modelo', $modeloNombre)
            ->count();

        if ($count > 0) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'El modelo ya existe para esta marca.'
            ], 409);
        }

        $usuario = $request->user();

        $modelo = Modelos::create([
            'fk_marca' => $datos['fk_marca'],
            'modelo' => $modeloNombre,
            'usuario_crea' => $usuario->usuario,

        ]);

        DB::commit();

        return response()->json([
            'ok' => true,
            'message' => 'Modelo registrado correctamente.',
            'data' => $modelo
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'ok' => false,
            'message' => 'No se pudo registrar el modelo',
            'error' => $e->getMessage(),
            'linea' => $e->getLine(),
            'archivo' => $e->getFile(),
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(Modelos $modelos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Modelos $modelos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Modelos $modelos)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modelos $modelos)
    {
        //
    }
}
