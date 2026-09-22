<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'inv_usuarios';

    protected $primaryKey = 'id_usuario';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'cedula',
        'apellido',
        'nombre',
        'email',
        'usuario',
        'password',
        'estado',
        'fk_despacho',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function despacho()
    {
        return $this->belongsTo(
            Despacho::class,
            'fk_despacho',
            'id_despacho'
        );
    }
}
