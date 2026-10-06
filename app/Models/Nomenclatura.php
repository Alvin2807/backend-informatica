<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Despacho;

class Nomenclatura extends Model
{
    protected $table = 'inv_nomenclaturas';

    protected $primaryKey = 'id_nomenclatura';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'fk_despacho',
        'nomenclatura',
        'fk_modelo'
    ];

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(
            Despacho::class,
            'fk_despacho',
            'id_despacho'
        );
    }
}
