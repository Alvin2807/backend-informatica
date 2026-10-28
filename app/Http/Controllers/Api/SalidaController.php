<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use App\Http\Requests\SolicitudSalida\RegistrarRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Detalle;
use App\Http\Requests\SolicitudSalida\EditarArticuloRequest;
use App\Http\Requests\SolicitudSalida\AgregarArticuloRequest;
use App\Models\Articulos;
use App\Models\InvNomenclatura;
use App\Models\Movimiento;
use App\Http\Requests\SolicitudSalida\ConfirmarRequest;
use Carbon\Carbon;
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

            /*
            |--------------------------------------------------------------------------
            | DATOS VALIDADOS
            |--------------------------------------------------------------------------
            */

            $datos = $request->validated();


            /*
            |--------------------------------------------------------------------------
            | USUARIO AUTENTICADO
            |--------------------------------------------------------------------------
            */

            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El usuario no está autenticado'
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | CALCULAR TOTAL SOLICITADO
            |--------------------------------------------------------------------------
            */

            $totalCantidadSolicitada = 0;

            foreach ($datos['detalles'] as $detalle) {

                $totalCantidadSolicitada +=
                    (int) $detalle['cantidad_solicitada'];
            }


            /*
            |--------------------------------------------------------------------------
            | CREAR SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud = Solicitud::create([

                'fk_tipo_solicitud' =>
                    $datos['fk_tipo_solicitud'],

                'fk_despacho' =>
                    $datos['fk_despacho'],

                'fk_nomenclatura' =>
                    $datos['fk_nomenclatura'],

                'entregado_por' =>
                    $datos['entregado_por'],

                'recibido_por' =>
                    $datos['recibido_por'],

                'incidencia' =>
                    $datos['incidencia'] ?? null,

                'fecha_solicitud' =>
                    $datos['fecha_solicitud'],

                'cantidad_solicitada' =>
                    $totalCantidadSolicitada,

                'usuario_crea' =>
                    strtoupper(
                        trim($usuario->usuario)
                    )
            ]);


            /*
            |--------------------------------------------------------------------------
            | CREAR DETALLES
            |--------------------------------------------------------------------------
            */

            $noItem = 1;

            foreach ($datos['detalles'] as $detalle) {

                Detalle::create([

                    'no_item' =>
                        $noItem,

                    'fk_solicitud' =>
                        $solicitud->id_solicitud,

                    'fk_articulo' =>
                        $detalle['fk_articulo'],

                    'cantidad_solicitada' =>
                        $detalle['cantidad_solicitada'],

                    'cantidad_despachada' =>
                        0,

                    'estado' =>
                        'Pendiente',

                    'usuario_crea' =>
                        strtoupper(
                            trim($usuario->usuario)
                        ),

                    'fecha_crea' =>
                        now()
                ]);

                $noItem++;
            }


            /*
            |--------------------------------------------------------------------------
            | CONFIRMAR TRANSACCIÓN
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | CONTAR SOLICITUDES PENDIENTES
            |--------------------------------------------------------------------------
            */

            $totalPendientes = Solicitud::where(
                'estado',
                'Pendiente'
            )->count();


            /*
            |--------------------------------------------------------------------------
            | RESPUESTA
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'ok' => true,

                'mensaje' =>
                    'Solicitud registrada correctamente',
                'data' => $solicitud,
                'totalPendiente' =>
                    $totalPendientes
            ], 201);


        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([

                'ok' => false,

                'mensaje' =>
                    'No se pudo registrar la solicitud',

                'error' =>
                    $th->getMessage()

            ], 500);
        }
    }

    public function agregarArticuloSalida(AgregarArticuloRequest $request,$id)
    {
        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | DATOS
            |--------------------------------------------------------------------------
            */

            $datos = $request->validated();


            /*
            |--------------------------------------------------------------------------
            | USUARIO
            |--------------------------------------------------------------------------
            */

            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El usuario no está autenticado.'
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR SOLICITUD
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR QUE EL ARTÍCULO NO EXISTA EN ESTA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $existe = Detalle::where(
                'fk_solicitud',
                $solicitud->id_solicitud
            )
            ->where(
                'fk_articulo',
                $datos['fk_articulo']
            )
            ->exists();


            if ($existe) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' =>
                        'El artículo ya existe en esta solicitud.'
                ], 409);
            }


            /*
            |--------------------------------------------------------------------------
            | ÚLTIMO ITEM
            |--------------------------------------------------------------------------
            */

            $ultimoItem = Detalle::where(
                'fk_solicitud',
                $solicitud->id_solicitud
            )
            ->max('no_item');


            $noItem = ($ultimoItem ?? 0) + 1;


            /*
            |--------------------------------------------------------------------------
            | CREAR DETALLE
            |--------------------------------------------------------------------------
            */

            $detalle = Detalle::create([

                'no_item' =>
                    $noItem,

                'fk_solicitud' =>
                    $solicitud->id_solicitud,

                'fk_articulo' =>
                    $datos['fk_articulo'],

                'cantidad_solicitada' =>
                    $datos['cantidad_solicitada'],

                'cantidad_despachada' =>
                    0,

                'estado' =>
                    'Pendiente',

                'usuario_crea' =>
                    strtoupper(
                        trim($usuario->usuario)
                    ),

                'fecha_crea' =>
                    now()
            ]);


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR TOTAL DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud->cantidad_solicitada =
                (int) $solicitud->cantidad_solicitada
                + (int) $datos['cantidad_solicitada'];

            $solicitud->usuario_modifica =
                strtoupper(
                    trim($usuario->usuario)
                );

            $solicitud->fecha_modifica =
                now();

            $solicitud->save();


            /*
            |--------------------------------------------------------------------------
            | CONFIRMAR
            |--------------------------------------------------------------------------
            */

            DB::commit();


            return response()->json([

                'ok' => true,

                'mensaje' =>
                    'Artículo agregado correctamente.',

                'data' => [

                    'id_solicitud' =>
                        $solicitud->id_solicitud,

                    'id_detalle' =>
                        $detalle->id_detalle,

                    'no_item' =>
                        $detalle->no_item,

                    'fk_articulo' =>
                        $detalle->fk_articulo,

                    'cantidad_solicitada' =>
                        $detalle->cantidad_solicitada,

                    'cantidad_total_solicitud' =>
                        $solicitud->cantidad_solicitada,

                    'estado' =>
                        $detalle->estado
                ]

            ], 201);


        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([

                'ok' => false,

                'mensaje' =>
                    'No se pudo agregar el artículo.',

                'error' =>
                    $th->getMessage()

            ], 500);
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

   public function editarArticulo(EditarArticuloRequest $request,$id)
   {
        try {

            DB::beginTransaction();

            $datos = $request->validated();

            $usuario = $request->user();

            /*
            |--------------------------------------------------------------------------
            | USUARIO
            |--------------------------------------------------------------------------
            */

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Usuario no autenticado'
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR DETALLE
            |--------------------------------------------------------------------------
            */

            $detalle = Detalle::where(
                'id_detalle',
                $id
            )->first();

            if (!$detalle) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El detalle no existe.'
                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud = Solicitud::where(
                'id_solicitud',
                $detalle->fk_solicitud
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


            /*
            |--------------------------------------------------------------------------
            | VALORES ANTERIORES
            |--------------------------------------------------------------------------
            */

            $articuloAnterior =
                $detalle->fk_articulo;

            $cantidadAnterior =
                (int) $detalle->cantidad_solicitada;


            /*
            |--------------------------------------------------------------------------
            | NUEVOS VALORES
            |--------------------------------------------------------------------------
            */

            $articuloNuevo =
                $datos['fk_articulo'] ?? $articuloAnterior;

            $cantidadNueva =
                isset($datos['cantidad_solicitada'])
                    ? (int) $datos['cantidad_solicitada']
                    : $cantidadAnterior;


            /*
            |--------------------------------------------------------------------------
            | VALIDAR ARTÍCULO DUPLICADO
            |--------------------------------------------------------------------------
            */

            if (
                $articuloNuevo != $articuloAnterior
            ) {

                $articuloExiste = Detalle::where(
                    'fk_solicitud',
                    $detalle->fk_solicitud
                )
                ->where(
                    'fk_articulo',
                    $articuloNuevo
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
            }


            /*
            |--------------------------------------------------------------------------
            | CALCULAR DIFERENCIA
            |--------------------------------------------------------------------------
            */

            $diferencia =
                $cantidadNueva - $cantidadAnterior;


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR DETALLE
            |--------------------------------------------------------------------------
            */

            $detalle->fk_articulo =
                $articuloNuevo;

            $detalle->cantidad_solicitada =
                $cantidadNueva;

            $detalle->usuario_modifica =
                strtoupper(
                    trim($usuario->usuario)
                );

            $detalle->fecha_modifica =
                now();

            $detalle->save();


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR TOTAL DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud->cantidad_solicitada =
                (int) $solicitud->cantidad_solicitada
                + $diferencia;

            $solicitud->usuario_modifica =
                strtoupper(
                    trim($usuario->usuario)
                );

            $solicitud->fecha_modifica =
                now();

            $solicitud->save();


            /*
            |--------------------------------------------------------------------------
            | CONFIRMAR
            |--------------------------------------------------------------------------
            */

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

                    'fk_articulo_anterior' =>
                        $articuloAnterior,

                    'fk_articulo_nuevo' =>
                        $articuloNuevo,

                    'cantidad_anterior' =>
                        $cantidadAnterior,

                    'cantidad_nueva' =>
                        $cantidadNueva,

                    'diferencia' =>
                        $diferencia,

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
    public function destroy(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | USUARIO AUTENTICADO
            |--------------------------------------------------------------------------
            */

            $usuario = $request->user();

            if (!$usuario) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El usuario no está autenticado.'
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR DETALLE POR id_detalle
            |--------------------------------------------------------------------------
            */

            $detalle = Detalle::where(
                'id_detalle',
                $id
            )->first();


            if (!$detalle) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El detalle no existe.',
                    'id_detalle_recibido' => $id
                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud = Solicitud::where(
                'id_solicitud',
                $detalle->fk_solicitud
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


            /*
            |--------------------------------------------------------------------------
            | GUARDAR INFORMACIÓN ANTES DE ELIMINAR
            |--------------------------------------------------------------------------
            */

            $idDetalle = $detalle->id_detalle;

            $idSolicitud = $detalle->fk_solicitud;

            $cantidadEliminar =
                (int) $detalle->cantidad_solicitada;


            /*
            |--------------------------------------------------------------------------
            | ELIMINAR DETALLE
            |--------------------------------------------------------------------------
            */

            $detalle->delete();


            /*
            |--------------------------------------------------------------------------
            | RESTAR CANTIDAD DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud->cantidad_solicitada =
                max(
                    0,
                    (int) $solicitud->cantidad_solicitada
                    - $cantidadEliminar
                );


            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA
            |--------------------------------------------------------------------------
            */

            $solicitud->usuario_modifica =
                strtoupper(
                    trim($usuario->usuario)
                );

            $solicitud->fecha_modifica = now();


            /*
            |--------------------------------------------------------------------------
            | GUARDAR SOLICITUD
            |--------------------------------------------------------------------------
            */

            $solicitud->save();


            /*
            |--------------------------------------------------------------------------
            | RENUMERAR LOS ITEMS
            |--------------------------------------------------------------------------
            */

            $detalles = Detalle::where(
                'fk_solicitud',
                $idSolicitud
            )
            ->orderBy(
                'no_item',
                'asc'
            )
            ->get();


            $noItem = 1;


            foreach ($detalles as $detalleItem) {

                $detalleItem->no_item =
                    $noItem;

                $detalleItem->usuario_modifica =
                    strtoupper(
                        trim($usuario->usuario)
                    );

                $detalleItem->fecha_modifica =
                    now();

                $detalleItem->save();

                $noItem++;
            }


            /*
            |--------------------------------------------------------------------------
            | CONFIRMAR TRANSACCIÓN
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | RESPUESTA
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'ok' => true,

                'mensaje' =>
                    'Artículo eliminado correctamente.',

                'data' => [

                    'id_solicitud' =>
                        $idSolicitud,

                    'id_detalle_eliminado' =>
                        $idDetalle,

                    'cantidad_eliminada' =>
                        $cantidadEliminar,

                    'cantidad_solicitada' =>
                        $solicitud->cantidad_solicitada
                ]

            ], 200);


        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([

                'ok' => false,

                'mensaje' =>
                    'No se pudo eliminar el artículo.',

                'error' =>
                    $th->getMessage()

            ], 500);
        }
    }



    public function confirmarSalida(ConfirmarRequest $request, $id)
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
            ->where('estado', 'Pendiente')
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
                        'Falta el id_detalle en el elemento'.$items
                    );
                }

                $detalle = Detalle::
                where('id_detalle', $items[$i]['id_detalle'])
                ->where('fk_solicitud', $id)
                ->lockForUpdate()
                ->first();

                if (!$detalle) {
                   throw new \RuntimeExecption(
                    'No se encontró el detalle'.$items[$i]['id_detalle']
                   );
                }

                $cantidadDespachada = (int) $detalle->cantidad_despachada;

                DB::table('inv_detalle')
                ->where('id_detalle', $items[$i]['id_detalle'])
                ->where('fk_solicitud', $id)
                ->update([
                    'cantidad_despachada'=> $items[$i]['cantidad_solicitada'] + $cantidadDespachada
                ]);

                $actualizados ++;

                $consultaDetalle = Detalle::
                select('id_detalle','cantidad_solicitada','cantidad_despachada','fk_articulo')
                ->where('id_detalle', $items[$i]['id_detalle'])
                ->where('fk_solicitud', $id)
                ->get();

                if ($consultaDetalle[0]['cantidad_solicitada'] === $consultaDetalle[0]['cantidad_despachada']) {
                    $detalleAct = new Detalle();
                    $dataDetalle['estado'] = 'Completado';
                    $dataDetalle['usuario_modifica'] = strtoupper($usuario->usuario);
                    $detalleAct = Detalle::where('id_detalle', $consultaDetalle[0]['id_detalle'])->update($dataDetalle);

                }

                $cantidadDespachadaSolicitud = (int) $solicitud->cantidad_despachada;

                $totalCantidadDespachadaDetalle = Detalle::
                where('fk_solicitud', $id)
                ->sum('cantidad_despachada');

                $actualizarSolicitud = new Solicitud();
                $dataSolicitud['fk_despacho'] = $request->input('fk_despacho');
                $dataSolicitud['fk_nomenclatura'] = $request->input('fk_nomenclatura');
                $dataSolicitud['entregado_por'] = strtoupper($request->input('entregado_por'));
                $dataSolicitud['recibido_por'] = strtoupper($request->input('recibido_por'));
                $dataSolicitud['incidencia'] = $request->input('incidencia');
                $dataSolicitud['fecha_solicitud'] = $request->input('fecha_solicitud');
                $dataSolicitud['cantidad_despachada'] = $totalCantidadDespachadaDetalle;
                $dataSolicitud['usuario_modifica'] = strtoupper($usuario->usuario);
                $actualizarSolicitud = Solicitud::where('id_solicitud', $id)->update($dataSolicitud);

                $consultaSolicitud = Solicitud::
                select('id_solicitud', 'cantidad_solicitada','cantidad_despachada')
                ->where('id_solicitud', $id)
                ->where('estado','Pendiente')
                ->get();

                if ($consultaSolicitud[0]['cantidad_solicitada'] === $consultaSolicitud[0]['cantidad_despachada']) {
                    $actualizarEstadoSolicitud = new Solicitud();
                    $dataEstado['estado'] = 'Completado';
                    $dataEstado['usuario_modifica'] = strtoupper($usuario->usuario);
                    $actualizarEstadoSolicitud = Solicitud::where('id_solicitud', $id)->update($dataEstado);
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
                $articulo['stock'] =  $consultaArticulo[0]['stock'] - $articuloDetalle[0]['cantidad_solicitada'];
                $articulo['usuario_modifica'] = strtoupper($usuario->usuario);
                $actualizarArticulo = Articulos::where('id_articulo', $consultaArticulo[0]['id_articulo'])->update($articulo);

                $fecha = now();
                $articuloId = $detalle->fk_articulo;
                $fechaSolicitud = Carbon::parse($solicitud->fecha_solicitud);
                $mes = $fechaSolicitud->month;
                $anio = $fechaSolicitud->year;

                $cantidadSalida = (int) $detalle->cantidad_solicitada;

                $consultaMovimiento = Movimiento::
                select('id_movimiento','fk_articulo','cantidad_salida')
                ->where('fk_articulo', $articuloId)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->get();

                if ($consultaMovimiento) {
                    $actualizarMovimiento = new Movimiento();
                    $dataMovi['cantidad_salida']  = $cantidadSalida + $consultaMovimiento[0]['cantidad_salida'];
                    $dataMovi['usuario_modifica'] = strtoupper($usuario->usuario);
                    $actualizarMovimiento = Movimiento::where('id_movimiento', $consultaMovimiento[0]['id_movimiento'])->update($dataMovi);

                } else {
                    $movimiento = new Movimiento();
                    $movimiento->fk_articulo = $articuloId;
                    $movimiento->mes = $mes;
                    $movimiento->anio = $anio;
                    $movimiento->cantidad_salida = $cantidadSalida;
                    $movimiento->usuario_crea = strtoupper($usuario->usuario);
                    $movimiento->save();
                }

                    $consultaNomenclaturaINV = InvNomenclatura::
                    select(
                        'id_inv_nomenclatura',
                        'fk_nomenclatura',
                        'mes',
                        'anio',
                        'cantidad_salida'
                    )
                    ->where('fk_nomenclatura', $solicitud->fk_nomenclatura)
                    ->where('mes', $mes)
                    ->where('anio', $anio)
                    ->lockForUpdate()
                    ->first();

                    if ($consultaNomenclaturaINV) {
                        $dataInvM['cantidad_salida'] =
                        (int) $cantidadSalida
                        + (int) $consultaNomenclaturaINV->cantidad_salida;

                        $dataInvM['usuario_modifica'] = strtoupper($usuario->usuario);

                        InvNomenclatura::where('id_inv_nomenclatura',$consultaNomenclaturaINV->id_inv_nomenclatura
                        )->update($dataInvM);
                    } else {
                        $invNomenclatura = new InvNomenclatura();
                        $invNomenclatura->fk_nomenclatura = $solicitud->fk_nomenclatura;
                        $invNomenclatura->mes = $mes;
                        $invNomenclatura->anio = $anio;
                        $invNomenclatura->cantidad_salida = $cantidadSalida;
                        $invNomenclatura->usuario_crea = strtoupper($usuario->usuario);
                        $invNomenclatura->save();
                    }

            }

            DB::commit();

            return response()->json([
                'ok'=>true,
                'data'=>$solicitud,
                'mensaje'=>'Se confirmó la solicitud',
                'detallesActualizados'=>$actualizados
            ],200);


        } catch (\Exception $th) {
            DB::rollback();
            return response()->json([
                'ok'=>false,
                'mensaje'=>'No se pudo confirmar la salida',
                'error'=>$th->getMessage(),

            ],500);
        }
    }

   public function eliminarSalida($id)
    {
        try {

            DB::beginTransaction();

            $solicitud = Solicitud::where('id_solicitud',$id)
            ->whereRaw(
                'UPPER(estado) = ?',
                ['PENDIENTE']
            )
            ->lockForUpdate()
            ->first();

            if (!$solicitud) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' =>
                        'La solicitud no existe o no está pendiente.',
                    'id_recibido' => $id
                ], 404);
            }

            $idSolicitud =
                $solicitud->id_solicitud;

            $detallesEliminados = DB::table(
                'inv_detalle'
            )
            ->where(
                'fk_solicitud',
                $idSolicitud
            )
            ->delete();

            $solicitudEliminada = DB::table(
                'inv_solicitud'
            )
            ->where(
                'id_solicitud',
                $idSolicitud
            )
            ->delete();

            if ($solicitudEliminada === 0) {

                DB::rollBack();

                return response()->json([
                    'ok' => false,
                    'mensaje' =>
                        'No se encontró la solicitud para eliminar.'
                ], 404);
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' =>
                    'Solicitud eliminada correctamente.',
                'data' => [
                    'id_solicitud' =>
                        $idSolicitud,
                    'detalles_eliminados' =>
                        $detallesEliminados,
                    'solicitud_eliminada' =>
                        $solicitudEliminada
                ]
            ], 200);

        } catch (\Exception $th) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'mensaje' =>
                    'No se pudo eliminar la solicitud.',
                'error' =>
                    $th->getMessage()
            ], 500);
        }
    }
}
