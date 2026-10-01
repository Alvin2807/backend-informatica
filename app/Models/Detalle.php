<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle extends Model
{
    protected $table      = 'inv_detalle';
    protected $primaryKey = 'id_detalle';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle',
        'fk_solicitud',
        'fk_articulo',
        'no_item',
        'cantidad_solicitada',
        'cantidad_despachada',
        'usuario_crea',
        'fecha_crea',
        'usuario_modifica',
        'fecha_modifica',
        'estado'
    ];
}
