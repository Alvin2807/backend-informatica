<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use Illuminate\Http\Request;
use App\Models\Provincia;

class DespachoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         try {

        $despachos = Despacho::with('provincia')
            ->whereHas('provincia', function ($query) {

                $query->where('provincia', 'COLÓN');

            })
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $despachos
        ], 200);

        } catch (\Exception $th) {

            return response()->json([
                'ok' => false,
                'mensaje' => 'No se pudo consultar los despachos',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function porProvincia($provincia)
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Despacho $despacho)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Despacho $despacho)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Despacho $despacho)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Despacho $despacho)
    {
        //
    }
}
