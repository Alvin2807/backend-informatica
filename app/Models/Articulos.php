<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Articulos extends Model
{
    protected $table = 'inv_articulos';
    protected $primarykey = 'id_articulo';
    protected $fillable =
    [
        'fk_categoria',
        'codigo',
        'modelo_insumo',
        'fk_categoria',
        'fk_marca',
        'fk_modelo',
        'fk_color',
        'detalle',
        'stock',
        'usuario_crea',
        'fecha_crea',
        'usuario_crea',
        'usuario_modifica',
        'fecha_modifica'
    ];

    protected $keyType = 'int';
    public $incrementig = true;
    public $timestamps = false;

    protected $casts = [
        'fk_marca'=>'integer',
        'fk_modelo'=>'integer',
        'fk_categoria'=>'integer',
        'fk_color'=>'integer',
        'stock'=>'integer'
    ];

}
