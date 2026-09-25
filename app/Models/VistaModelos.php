<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaModelos extends Model
{
    protected $table = 'vista_modelos_impresoras';

    protected $casts = [
        'fk_marca'=>'integer',
        'id_modelos'=>'integer'
    ];
}
