<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSolicitud extends Model
{
    protected $table = 'inv_tipo_solicitud';

    protected $primaryKey = 'id_tipo_solicitud';

    public $timestamps = false;

    protected $fillable = [
        'tipo_solicitud',
    ];


}
