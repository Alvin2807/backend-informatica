<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table      = 'inv_colores';
    protected $primarykey = 'id_color';

    public $incrementing = true;
    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'color'
    ];
}
