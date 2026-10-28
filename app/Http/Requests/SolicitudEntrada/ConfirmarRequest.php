<?php

namespace App\Http\Requests\SolicitudEntrada;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmarRequest extends FormRequest
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
            'num_solicitud'=>'string|required',
            'entregado_por'=>'string|required',
            'recibido_por'=>'string|required',
            'detalles'=>[
                'required',
                'array',
                'min:1'
            ],
            'detalles.*.fk_articulo'=>'required|integer|exists:inv_articulos,id_articulo',
            'detalles.*.cantidad_solicitada'=>'required|integer',
            'detalles.*.id_detalle'=>'required|integer|exists:inv_detalle,id_detalle'
        ];
    }
}
