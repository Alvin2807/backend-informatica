<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Detalle;

class Solicitud extends Model
{
    protected $table = 'inv_solicitud';
    protected $primaryKey = 'id_solicitud';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fk_tipo_solicitud',
        'fk_despacho',
        'fk_nomenclatura',
        'num_solicitud',
        'entregado_por',
        'recibido_por',
        'incidencia',
        'cantidad_solicitada',
        'cantidad_despachada',
        'fecha_solicitud',
        'usuario_crea',
        'fecha_modifica',
        'usuario_modifica',
        'fecha_modifica',
        'estado'
    ];

    public function detalles()
    {
        return $this->hasMany(
            Detalle::class,
            'fk_solicitud',
            'id_solicitud'
        );
        /* return $this->hasMany(
            Detalle::class,
            'fk_solicitud',
            'id_solicitud'
        ); */
    }

}
