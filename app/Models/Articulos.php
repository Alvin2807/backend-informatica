<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Articulos extends Model
{
    protected $table = 'inv_articulos';

    protected $primaryKey = 'id_articulo';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'fk_categoria',
        'fk_color',
        'fk_modelo',
        'fk_marca',
        'codigo',
        'modelo_insumo',
        'detalle',
        'stock',
        'usuario_crea',
        'fecha_crea',
        'usuario_modifica',
        'fecha_modifica'
    ];

    protected $casts = [
        'fk_marca'=>'integer',
        'fk_modelo'=>'integer',
        'fk_categoria'=>'integer',
        'fk_color'=>'integer',
        'stock'=>'integer'
    ];

}
