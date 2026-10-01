<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use App\Http\Requests\SolicitudEntrada\RegistrarRequest;
use Illuminate\Support\Facades\DB;
use App\Models\TipoSolicitud;
use App\Models\Detalle;
use App\Models\VistaSolicitud;
use App\Http\Requests\Solicitud\RegistrarDetalleRequest;
class SolicitudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $solicitud = VistaSolicitud::select(
            'id_solicitud','fk_tipo_solicitud','fk_despacho','tipo_solicitud','despacho','fk_nomenclatura','num_solicitud',
            'entregado_por','recibido_por','incidencia','nomenclatura','cantidad_solicitada','cantidad_despachada','fecha_solicitud',
            'estado'
        )
        ->where('estado','Pendiente')
        ->orderBy('id_solicitud','desc')
        ->get();
        return response()->json([
            'ok'=>true,
            'data'=>$solicitud
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
    public function store(RegistrarRequest $request)
    {

        try {
             DB::beginTransaction();

             $datos = $request->validated();


            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'message' => 'Usuario no autenticado.'
                ], 401);
            }



            $totalCantidadSolicitada = 0;

            foreach ($datos['detalles'] as $detalle) {

                $totalCantidadSolicitada +=
                    $detalle['cantidad_solicitada'];
            }


            $solicitud = Solicitud::create([

                'fk_tipo_solicitud' =>
                    $datos['fk_tipo_solicitud'],

                'fk_despacho' =>
                    $datos['fk_despacho'],

                'num_solicitud' =>
                    strtoupper(trim($datos['num_solicitud'])),

                'entregado_por' =>
                    $datos['entregado_por'],

                'recibido_por' =>
                    $datos['recibido_por'],

                'fecha_solicitud' =>
                    $datos['fecha_solicitud'],

                'cantidad_solicitada' =>
                    $totalCantidadSolicitada,

                'usuario_crea' =>
                    strtoupper($usuario->usuario),

            ]);

            $noItem = 1;

            foreach ($datos['detalles'] as $detalle) {
                Detalle::create([
                    'no_item'=>$noItem,
                    'fk_solicitud'=>$solicitud->id,
                    'fk_articulo'=>$detalle['fk_articulo'],
                    'cantidad_solicitada'=>$detalle['cantidad_solicitada'],
                    'usuario_crea'=>strtoupper(trim($usuario->usuario))
                ]);
                $noItem++;
            }


             DB::commit();

            return response()->json([
                'ok' => true,
                'message' =>'Solicitud registrada correctamente.',
                'data'=>$solicitud
            ], 201);


        } catch (\Exception $th) {
             DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' =>
                    'No se pudo registrar la solicitud.',

                'error' =>
                    $th->getMessage()

            ], 500);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $solicitud = Solicitud::with('detalles')
            ->where('id_solicitud', $id)
            ->where('estado','Pendiente')
            ->first();

            if (!$solicitud) {

                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'La solicitud no existe'
                ],404);
            }

            return response()->json([
                'ok'=>true,
                'data'=>$solicitud
            ],200);

        } catch (\Exception $th) {

            return response->json([
                'ok'=>false,
                'mensaje'=>'No se pudo consultar la solicitud',
                'error'=>$th->getMessage()
            ]);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Solicitud $solicitud)
    {

    }

    public function agregarArticulo(RegistrarDetalleRequest $request, $id)
    {
        try {

            DB::beginTransaction();

            $datos = $request->validated();

            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El usuario no está autenticado.'
                ], 401);
            }

            $solicitud = Solicitud::where('id_solicitud', $id)
                ->where('estado', 'Pendiente')
                ->first();

            if (!$solicitud) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'La solicitud no existe o no está pendiente.'
                ], 404);
            }

            $existe = Detalle::where('fk_solicitud', $id)
                ->where('fk_articulo', $datos['fk_articulo'])
                ->exists();

            if ($existe) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El artículo ya existe en esta solicitud.'
                ], 409);
            }

            $ultimoItem = Detalle::where('fk_solicitud', $id)
                ->max('no_item');

            $noItem = ($ultimoItem ?? 0) + 1;

            $detalle = Detalle::create([
                'no_item' => $noItem,
                'fk_solicitud' => $solicitud->id_solicitud,
                'fk_articulo' => $datos['fk_articulo'],
                'cantidad_solicitada' => $datos['cantidad_solicitada'],
                'usuario_crea' => strtoupper(trim($usuario->usuario)),
            ]);

            $solicitud->cantidad_solicitada +=
                $datos['cantidad_solicitada'];

            $solicitud->usuario_modifica =
                strtoupper(trim($usuario->usuario));
            $solicitud->save();

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' => 'Artículo agregado correctamente.',
                'data' => $detalle
            ], 201);

        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' => 'No se pudo agregar el artículo.',
                'error' => $th->getMessage()
            ], 500);
        }
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
    public function destroy(Request $request, $id)
    {
     try {

        DB::beginTransaction();

        $usuario = $request->user();

        if (!$usuario) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' => 'El usuario no está autenticado.'
            ], 401);
        }

        // Buscar el detalle
        $detalle = Detalle::where('id_detalle', $id)
            ->first();

        if (!$detalle) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' => 'El artículo no existe en el detalle.'
            ], 404);
        }

        // Buscar la solicitud
        $solicitud = Solicitud::where(
            'id_solicitud',
            $detalle->fk_solicitud
        )->first();

        if (!$solicitud) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' => 'La solicitud no existe.'
            ], 404);
        }

        // Guardar cantidad antes de eliminar
        $cantidad = $detalle->cantidad_solicitada;

        // Restar cantidad de la solicitud
        $solicitud->cantidad_solicitada -= $cantidad;

        if ($solicitud->cantidad_solicitada < 0) {
            $solicitud->cantidad_solicitada = 0;
        }

        $solicitud->usuario_modifica =
            strtoupper(trim($usuario->usuario));

        $solicitud->fecha_modifica = now();

        $solicitud->save();

        // Eliminar el detalle
        $detalle->delete();

        // Obtener los detalles restantes
        $detalles = Detalle::where(
            'fk_solicitud',
            $solicitud->id_solicitud
        )
        ->orderBy('no_item')
        ->get();

        // Renumerar desde 1
        $noItem = 1;

        foreach ($detalles as $detalleItem) {

            $detalleItem->no_item = $noItem;

            $detalleItem->usuario_modifica =
                strtoupper(trim($usuario->usuario));

            $detalleItem->fecha_modifica = now();

            $detalleItem->save();

            $noItem++;
        }

        DB::commit();

        return response()->json([
            'ok' => true,
            'mensaje' => 'Artículo eliminado correctamente.',
            'data' => [
                'id_solicitud' =>
                    $solicitud->id_solicitud,

                'cantidad_solicitada' =>
                    $solicitud->cantidad_solicitada
            ]
        ], 200);

     } catch (\Exception $th) {

        DB::rollBack();

        return response()->json([
            'ok' => false,
            'mensaje' => 'No se pudo eliminar el artículo.',
            'error' => $th->getMessage()
        ], 500);
     }
    }
}
