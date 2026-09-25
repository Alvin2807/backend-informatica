<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'inv_categorias';
    protected $primarykey = 'id_categoria';

    public $incrementing = true;
    public $timestamps = false;

    protected $keyType = 'int';


    protected $fillable = [
        'categoria'
    ];
}
