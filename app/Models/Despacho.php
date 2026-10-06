<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Nomenclatura;
use App\Models\Provincia;

class Despacho extends Model
{
    protected $table = 'inv_despachos';

    protected $primaryKey = 'id_despacho';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'fk_provincia',
        'despacho',
    ];

    public function nomenclaturas(): HasMany
    {
        return $this->hasMany(
            Nomenclatura::class,
            'fk_despacho',
            'id_despacho'
        );
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(
            Provincia::class,
            'fk_provincia',
            'id_provincia'
        );
    }
}
