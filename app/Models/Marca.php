<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'inv_marcas';
    protected $primarykey = 'id_marca';
    protected $fillable = ['marca'];
    public $incrementing = true;
    public $timestamps = false;
    protected $keyType = 'int';

    public function modelos()
    {
        return $this->hasMany(
            Modelos::class,
            'fk_marca',
            'id_marca'
        );
    }

    /* public function modelo(){
        return $this->belongsTo(
            Modelos::class,
            'fk_marca','id_marca'
        );
    }
 */

}
