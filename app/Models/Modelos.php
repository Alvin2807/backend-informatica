<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modelos extends Model
{
    protected $table = 'inv_modelos';
    protected $primarykey = 'id_modelo';
    protected $fillable = ['fk_marca','modelo','usuario_crea','usuario_modifica','fecha_crea', 'usuario_modifica',
        'fecha_modifica',];
    public $incrementing = true;
    public $timestamps = false;
    protected $keyType = 'int';

    protected $casts = [
        'fk_marca'=>'integer',
    ];

     public function marca()
    {
        return $this->belongsTo(
            InvMarca::class,
            'fk_marca',
            'id_marca'
        );
    }

   /*  public function marca(){
        return $this->belongsTo(
            Marca::class,
            'fk_marca','id_marca'
        );
    }
 */
}
