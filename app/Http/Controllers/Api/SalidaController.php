<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use App\Http\Requests\SolicitudSalida\RegistrarRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Detalle;

class SalidaController extends Controller
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
    public function store(RegistrarRequest $request)
    {
        try {

            DB::beginTransaction();

            $datos = $request->validated();

            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'El usuario no está autenticado'
                ],401);
            }

            $totalCantidad_solicitada = 0;

            foreach ($datos['detalles'] as $detalle) {
                $totalCantidad_solicitada +=
                $detalle['cantidad_solicitada'];
            }

            $solicitud = Solicitud::create([
                'fk_tipo_solicitud'=>$datos['fk_tipo_solicitud'],
                'fk_despacho'=>$datos['fk_despacho'],
                'fk_nomenclatura'=>$datos['fk_nomenclatura'],
                'entregado_por'=>$datos['entregado_por'],
                'recibido_por'=>$datos['recibido_por'],
                'incidencia'=>$datos['incidencia'],
                'fecha_solicitud'=>$datos['fecha_solicitud'],
                'cantidad_solicitada'=>$totalCantidad_solicitada,
                'usuario_crea'=>strtoupper($usuario->usuario)
            ]);

            $noItem = 1;

            foreach ($datos['detalles'] as $detalle) {
                Detalle::create([
                    'no_item'=>$noItem,
                    'fk_solicitud'=>$solicitud->id_solicitud,
                    'fk_articulo'=>$detalle['fk_articulo'],
                    'cantidad_solicitada'=>$detalle['cantidad_solicitada'],
                    'usuario_crea'=>strtoupper(trim($usuario->usuario))
                ]);

                $noItem ++;

                DB::commit();

                $totalPendientes = Solicitud::where('estado', 'Pendiente')->count();

                return response()->json([
                    'ok'=>true,
                    'mensaje'=>'Solicitud registrada correctamente',
                    'data'=>$solicitud,
                    'totalPendiente'=>$totalPendientes
                ],201);
            }
        } catch (\Exception $th) {
            DB::rollback();

            return response()->json([
                'ok'=>false,
                'mensaje'=>'No se pudo registrar la solicitud',
                'error'=>$th->getMessage()
            ],500);
        }
    }

    public function editarSalidaEncabezado(Request $request, $id)
    {
      try {

        DB::beginTransaction();

        $datos = $request->validate([
            'fk_tipo_solicitud'=>'required|integer|exists:inv_tipo_solicitud,id_tipo_solicitud',
            'fk_despacho'=>'required|integer|exists:inv_despachos,id_despacho',
            'fk_nomenclatura'=>'required|integer|exists:inv_nomenclaturas,id_nomenclatura',
            'entregado_por'=>'required|string',
            'recibido_por'=>'nullable|string',
            'incidencia'=>'required|integer',
            'fecha_solicitud'=>'required|date'
        ]);

        $usuario = $request->user();

        if (!$usuario) {
            DB::rollback();

            return response()->json([
                'ok'=>false,
                'mensaje'=>'El usuario no está autenticado',
            ],401);
        }

        $solicitud = Solicitud::where('id_solicitud',$id)
        ->where('estado','Pendiente')
        ->first();

        if (!$solicitud) {
           DB::rollback();

           return response()->json([
            'ok'=>false,
            'mensaje'=>'La solicitud no existe o no está pendiente.'
           ],404);
        }

        $existe = Solicitud::where(
           'incidencia', $datos['incidencia']
        )->where('id_solicitud', '<>', $solicitud->id_solicitud)->exists();

        if ($existe) {
            DB::rollback();

            return response()->json([
                'ok'=>false,
                'mensaje'=>'El número de incidencia ya existe.'
            ],409);
        }

        $solicitud->fk_despacho = $datos['fk_despacho'];
        $solicitud->fk_nomenclatura = $datos['fk_nomenclatura'];
        $solicitud->entregado_por = strtoupper(trim($datos['entregado_por']));
        $solicitud->recibido_por = strtoupper(trim($datos['recibido_por']));
        $solicitud->incidencia = $datos['incidencia'];
        $solicitud->usuario_modifica = strtoupper(trim($usuario->usuario));
        $solicitud->save();

        DB::commit();

        return response()->json([
            'ok'=>true,
            'mensaje'=>'Encabezado actualizado correctamente.',
            'data'=>$solicitud
        ],200);

      } catch (\Exception $th) {
        DB::rollback();

        return response()->json([
            'ok'=>false,
            'mensaje'=>'No se pudo actualizar el encabezado.',
            'error'=>$th->getMessage()
        ],500);
      }
    }

    /**
     * Display the specified resource.
     */
    public function show(Solicitud $solicitud)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Solicitud $solicitud)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Solicitud $solicitud)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Solicitud $solicitud)
    {
        //
    }
}
