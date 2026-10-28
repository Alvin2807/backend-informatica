<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvNomenclatura extends Model
{
    public $table = 'inv_inventario_nomenclaturas';

    protected $primaryKey = 'id_inv_nomenclatura';

    protected $fillable = [
        'id_inv_nomenclatura',
        'fk_nomenclatura',
        'mes',
        'anio',
        'usuario',
        'fecha_crea',
        'usuario_modifica',
        'fecha_modifica',
        'cantidad_salida'
    ];

    protected $keyType = 'int';

    public $timestamps = false;

    public $incrementing = true;

    protected $casts = [
        'fk_nomenclatura' => 'integer',
        'cantidad_salida' => 'integer'
    ];
}
