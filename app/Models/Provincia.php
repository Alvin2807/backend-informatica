<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Despacho;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provincia extends Model
{
    protected $table = 'inv_provincias';
    protected $primaryKey = 'id_provincia';
    protected $keyType = 'int';
    protected $fillable = ['provincia'];
    public $timestamps = false;

    public function Despachos(): HasMany{
        return $this->hasMany(
            Despacho::class,
            'id_provincia',
            'fk_provincia'
        );
    }


}
