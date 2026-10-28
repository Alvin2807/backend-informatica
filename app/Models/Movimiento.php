<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    public    $table = 'inv_movimiento';
    protected $keyType = 'int';
    protected $primaryKey = 'id_movimiento';
    protected $fillable = [
        'fk_articulo',
        'tipo_movimiento',
        'cantidad_entrada',
        'cantidad_salida',
        'anio',
        'mes',
        'usuario_crea',
        'fecha_crea',
    ];
    public $incrementing = true;
    public $timestamps = false;

   protected $casts = [
        'id_movimiento' => 'integer',
        'fk_articulo' => 'integer',
        'cantidad_entrada'=>'integer',
        'cantidad_salida'=>'integer'
    ];

}
