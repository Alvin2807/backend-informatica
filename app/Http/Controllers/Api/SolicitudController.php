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
use App\Http\Requests\SolicitudEntrada\EditarDetalleRequest;
use App\Http\Requests\SolicitudEntrada\EditarSolicitudRequest;
use App\Models\Articulos;
use App\Models\Despacho;
use Carbon\Carbon;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SolicitudEntrada\ConfirmarRequest;
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

    public function obTnerDatos()
    {
        $year = Carbon::now()->year;
        return response()->json([
            'data'=>$year
        ]);
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
                   strtoupper(trim($datos['entregado_por'])),

                'recibido_por' =>
                  strtoupper(trim($datos['recibido_por'])),

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
                    'fk_solicitud'=>$solicitud->id_solicitud,
                    'fk_articulo'=>$detalle['fk_articulo'],
                    'cantidad_solicitada'=>$detalle['cantidad_solicitada'],
                    'usuario_crea'=>strtoupper(trim($usuario->usuario))
                ]);
                $noItem++;
            }


             DB::commit();

             $totalPendientes = Solicitud::where('estado', 'Pendiente')->count();

            return response()->json([
                'ok' => true,
                'message' =>'Solicitud registrada correctamente.',
                'data'=>$solicitud,
                'totalPendiente'=>$totalPendientes
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

   public function editarArticulo(EditarDetalleRequest $request, $id)
    {
        try {

            DB::beginTransaction();

            $datos = $request->validated();

            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Usuario no autenticado.'
                ], 401);
            }

            // Buscar detalle
            $detalle = Detalle::where('id_detalle', $id)->first();

            if (!$detalle) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El detalle no existe.'
                ], 404);
            }

            // Buscar solicitud
            $solicitud = Solicitud::where(
                'id_solicitud',
                $detalle->fk_solicitud
            )
            ->where('estado', 'Pendiente')
            ->first();

            if (!$solicitud) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' =>
                        'La solicitud no existe o no está pendiente.'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | VERIFICAR ARTÍCULO SOLAMENTE SI REALMENTE CAMBIÓ
            |--------------------------------------------------------------------------
            */

            if (
                isset($datos['fk_articulo']) &&
                $datos['fk_articulo'] != $detalle->fk_articulo
            ) {

                $articuloExiste = Detalle::where(
                    'fk_solicitud',
                    $detalle->fk_solicitud
                )
                ->where(
                    'fk_articulo',
                    $datos['fk_articulo']
                )
                ->where(
                    'id_detalle',
                    '!=',
                    $detalle->id_detalle
                )
                ->exists();

                if ($articuloExiste) {

                    DB::rollBack();

                    return response()->json([
                        'ok' => false,
                        'mensaje' =>
                            'El artículo ya existe en otro item de esta solicitud.'
                    ], 409);
                }

                // Cambiar artículo
                $detalle->fk_articulo =
                    $datos['fk_articulo'];
            }

            /*
            |--------------------------------------------------------------------------
            | CANTIDAD
            |--------------------------------------------------------------------------
            */

            $cantidadAnterior =
                $detalle->cantidad_solicitada;

            $cantidadNueva =
                $datos['cantidad_solicitada'];

            $detalle->cantidad_solicitada =
                $cantidadNueva;

            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA
            |--------------------------------------------------------------------------
            */

            $detalle->usuario_modifica =
                strtoupper(trim($usuario->usuario));


            $detalle->save();

            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR TOTAL DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $diferencia =
                $cantidadNueva - $cantidadAnterior;

            $solicitud->cantidad_solicitada +=
                $diferencia;

            $solicitud->usuario_modifica =
                strtoupper(trim($usuario->usuario));

            $solicitud->fecha_modifica =
                now();

            $solicitud->save();

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' =>
                    'Artículo actualizado correctamente.',
                'data' => [
                    'id_detalle' =>
                        $detalle->id_detalle,

                    'no_item' =>
                        $detalle->no_item,

                    'fk_articulo' =>
                        $detalle->fk_articulo,

                    'cantidad_anterior' =>
                        $cantidadAnterior,

                    'cantidad_nueva' =>
                        $cantidadNueva,

                    'cantidad_total_solicitud' =>
                        $solicitud->cantidad_solicitada
                ]
            ], 200);

        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'No se pudo actualizar el artículo.',
                'error' =>
                    $th->getMessage()
            ], 500);
        }
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


    public function editarSolicitud(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $datos = $request->validate([
                'fk_tipo_solicitud' => 'required|integer|exists:inv_tipo_solicitud,id_tipo_solicitud',

                'fk_despacho' => 'required|integer|exists:inv_despachos,id_despacho',

                'num_solicitud' => 'required|string|max:100',

                'entregado_por' => 'required|string|max:100',

                'recibido_por' => 'required|string|max:100',

                'fecha_solicitud' => 'required|date',
            ]);

            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Usuario no autenticado.'
                ], 401);
            }

            $solicitud = Solicitud::where(
                'id_solicitud',
                $id
            )
            ->where(
                'estado',
                'Pendiente'
            )
            ->first();

            if (!$solicitud) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' =>
                        'La solicitud no existe o no está pendiente.'
                ], 404);
            }

            // Verificar número duplicado
            $existe = Solicitud::where(
                'num_solicitud',
                strtoupper(trim($datos['num_solicitud']))
            )
            ->where(
                'id_solicitud',
                '!=',
                $solicitud->id_solicitud
            )
            ->exists();

            if ($existe) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' =>
                        'El número de solicitud ya existe.'
                ], 409);
            }

            // Editar encabezado
            $solicitud->fk_tipo_solicitud =
                $datos['fk_tipo_solicitud'];

            $solicitud->fk_despacho =
                $datos['fk_despacho'];

            $solicitud->num_solicitud =
                strtoupper(trim($datos['num_solicitud']));

            $solicitud->entregado_por =
                strtoupper(trim($datos['entregado_por']));

            $solicitud->recibido_por =
                strtoupper(trim($datos['recibido_por']));

            $solicitud->fecha_solicitud =
                $datos['fecha_solicitud'];

            $solicitud->usuario_modifica =
                strtoupper(trim($usuario->usuario));

            $solicitud->save();

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' =>
                    'Encabezado actualizado correctamente.',
                'data' => $solicitud
            ], 200);

        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'No se pudo actualizar el encabezado.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function eliminarSolicitud($id)
    {
        try {

            DB::beginTransaction();

            // Buscar solicitud
            $solicitud = Solicitud::where(
                'id_solicitud',
                $id
            )
            ->where(
                'estado',
                'Pendiente'
            )
            ->first();

            if (!$solicitud) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'La solicitud no existe o no está pendiente.'
                ], 404);
            }

            // Guardar el ID antes de eliminar
            $idSolicitud = $solicitud->id_solicitud;

            // Eliminar detalles
            $detallesEliminados = DB::table('inv_detalle')
                ->where('fk_solicitud', $idSolicitud)
                ->delete();

            // Eliminar solicitud
            $solicitudEliminada = DB::table('inv_solicitud')
                ->where('id_solicitud', $idSolicitud)
                ->delete();

            // Verificar que realmente se eliminó
            if ($solicitudEliminada === 0) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'No se encontró la solicitud para eliminar.'
                ], 404);
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' => 'Se eliminó la solicitud.',
                'data' => [
                    'id_solicitud' => $idSolicitud,
                    'detalles_eliminados' => $detallesEliminados,
                    'solicitud_eliminada' => $solicitudEliminada
                ]
            ], 200);

        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' => 'No se pudo eliminar la solicitud.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function confirmarSolicitud(ConfirmarRequest $request, $id)
    {
        try {

            $usuario = $request->user();

            if (!$usuario) {
                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'El usuario no está autenticado'
                ],401);

            }

            $items = $request->input('detalles', []);

            if (!is_array($items) || count($items) === 0) {
                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'Faltan los detalles de la solicitud'
                ],422);
            }

            DB::beginTransaction();

            $solicitud = Solicitud::
            where('id_solicitud', $id)
            ->where('estado','Pendiente')
            ->lockForUpdate()
            ->first();

            if (!$solicitud) {
                DB::rollback();
                return response()->json([
                    'ok'=>false,
                    'mensaje'=>'La solicitud no existe o no está pendiente'

                ],404);
            }

            $actualizados = 0;

            for ($i=0; $i <count($items) ; $i++) {
                if (!isset($items[$i]['id_detalle'])) {
                    throw new \RuntimeException(
                        'Falta el id_detalle en el elemento ' . $i
                    );
                }

                $detalle = Detalle::
                    where('id_detalle', $items[$i]['id_detalle'])
                    ->where('fk_solicitud', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$detalle) {
                    throw new \RuntimeException(
                        'No se encontró el detalle ' .
                        $items[$i]['id_detalle']
                    );
                }

                $cantidadDespachada = (int) $detalle->despachada;

                DB::table('inv_detalle')
                ->where('id_detalle', $items[$i]['id_detalle'])
                ->where('fk_solicitud', $id)
                ->update([
                    'cantidad_despachada' => $items[$i]['cantidad_solicitada'] + $cantidadDespachada
                ]);

                $actualizados ++;

                $consultaDetalle = Detalle::
                select('id_detalle','cantidad_despachada','cantidad_solicitada')
                ->where('id_detalle', $items[$i]['id_detalle'])
                ->where('fk_solicitud', $id)
                ->get();

                if ($consultaDetalle[0]['cantidad_solicitada'] === $consultaDetalle[0]['cantidad_despachada']) {
                   $DetalleAct = new Detalle();
                   $actualizarDetalle['estado'] = 'Completado';
                   $DetalleAct = Detalle::where('id_detalle', $consultaDetalle[0]['id_detalle'])->update($actualizarDetalle);

                }

                $cantidadDespachadaSolicitud = (int) $solicitud->cantidad_despachada;

                $totalCantidadDespachadaDetalle = Detalle::
                where('fk_solicitud', $id)
                ->sum('cantidad_despachada');

                $actualizarSolicitudDatos = new Solicitud();
                $actualizarSolicitud['fk_despacho'] = $request->input('fk_despacho');
                $actualizarSolicitud['num_solicitud'] = strtoupper($request->input('num_solicitud'));
                $actualizarSolicitud['entregado_por'] = strtoupper($request->input('entregado_por'));
                $actualizarSolicitud['recibido_por']  = strtoupper($request->input('recibido_por'));
                $actualizarSolicitud['cantidad_despachada'] = $totalCantidadDespachadaDetalle;
                $actualizarSolicitud['usuario_modifica'] = strtoupper($usuario->usuario);
                $actualizarSolicitudDatos = Solicitud::where('id_solicitud', $id)->update($actualizarSolicitud);

                $consultaSolicitud = Solicitud::
                select('id_solicitud','cantidad_solicitada','cantidad_despachada')
                ->where('id_solicitud', $id)
                ->where('estado','Pendiente')
                ->get();

                if ($consultaSolicitud[0]['cantidad_solicitada'] === $consultaSolicitud[0]['cantidad_despachada']) {
                    $actualizarSolicitudEstado = new Solicitud();
                    $datosSolicitud['estado'] = 'Completado';
                    $datosSolicitud['usuario_modifica'] = strtoupper($usuario->usuario);
                    $actualizarSolicitudEstado = Solicitud::where('id_solicitud', $id)->update($datosSolicitud);

                }

                $articuloDetalle = Detalle::
                select('id_detalle','fk_articulo','cantidad_solicitada','cantidad_despachada')
                ->where('fk_articulo', $items[$i]['fk_articulo'])
                ->where('fk_solicitud', $id)
                ->get();

                if (!$articuloDetalle) {

                   return response()->json([
                    'ok'=>false,
                    'mensaje'=>'El artículo no existe en esta solicitud'
                   ],404);
                }

                $consultaArticulo = Articulos::
                select('id_articulo','stock')
                ->where('id_articulo', $items[$i]['fk_articulo'])
                ->get();

                $actualizarArticulo = new Articulos();
                $articulo['stock'] =  $consultaArticulo[0]['stock'] + $articuloDetalle[0]['cantidad_solicitada'];
                $articulo['usuario_modifica'] = strtoupper($usuario->usuario);
                $actualizarArticulo = Articulos::where('id_articulo', $consultaArticulo[0]['id_articulo'])->update($articulo);

                /*
                    Registrar movimientos
                */

                $fecha = now();
                $articuloId = $detalle->fk_articulo;
                // Obtener mes y año de fecha_solicitud
                $fechaSolicitud = Carbon::parse($solicitud->fecha_solicitud);
                $mes = $fechaSolicitud->month;
                $anio = $fechaSolicitud->year;

                $cantidadEntrada = (int) $detalle->cantidad_solicitada;

                $consultaMovimiento = Movimiento::
                select('id_movimiento','fk_articulo','cantidad_entrada')
                ->where('fk_articulo', $articuloId)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->get();

                if ($consultaMovimiento) {
                    $actualizarMovimiento = new Movimiento();
                    $dataMovi['cantidad_entrada'] = $cantidadEntrada + $consultaMovimiento[0]['cantidad_entrada'];
                    $dataMovi['usuario_modifica'] = strtoupper($usuario->usuario);
                    $actualizarMovimiento = Movimiento::where('id_movimiento', $consultaMovimiento[0]['id_movimiento'])->update($dataMovi);
                } else {

                    $movimiento = new Movimiento();
                    $movimiento->fk_articulo = $articuloId;
                    $movimiento->mes = $mes;
                    $movimiento->anio = $anio;
                    $movimiento->cantidad_entrada = $cantidadEntrada;
                    $movimiento->usuario_crea = strtoupper($usuario->usuario);
                    $movimiento->save();
                }


            }

            DB::commit();

            return response()->json([
                'ok'=>true,
                'data'=>$solicitud,
                'mensaje'=>'Se confirmó la solicitud',
                'detalleActualizados'=> $actualizados
            ],200);


        } catch (\Exception $th) {
            DB::rollback();
            return response()->json([
                'ok'=>false,
                'mensaje'=>'No se pudo confirmar la solicitud',
                'error'=>$th->getMessage()
            ],500);
        }
    }



}
