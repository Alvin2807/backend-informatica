<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaSolicitud extends Model
{
    protected $table = 'vista_solicitud';
    protected $casts = [
        'fecha_solicitud'=>'datetime:d-m-Y'
    ];

}
