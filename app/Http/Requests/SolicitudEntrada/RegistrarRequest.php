<?php

namespace App\Http\Requests\SolicitudEntrada;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fk_tipo_solicitud'=>'required|integer|exists:inv_tipo_solicitud,id_tipo_solicitud',
            'fk_despacho'=>'required|integer|exists:inv_despachos,id_despacho',
            'num_solicitud'=>'required|string|max:100|unique:inv_solicitud,num_solicitud',
            'entregado_por'=>'required|string|max:100',
            'recibido_por'=>'required|string|max:100',
            'fecha_solicitud'=>'required|date',
            'detalles'=>[
                'required',
                'array',
                'min:1'
            ],
            'detalles.*.fk_articulo'=>'required|integer|exists:inv_articulos,id_articulo',
            'detalles.*.cantidad_solicitada'=>'required|integer'
        ];

    }

    public function messages():array{
        return [
            'num_solicitud.unique'=>'El número de solicitud ya existe'
        ];
    }
}
