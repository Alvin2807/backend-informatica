<?php

namespace App\Http\Requests\SolicitudSalida;

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
            'fk_despacho'=> 'required|integer|exists:inv_despachos,id_despacho',
            'fk_nomenclatura'=>'required|integer|exists:inv_nomenclaturas,id_nomenclatura',
            'entregado_por'=>'required|string|max:100',
            'recibido_por'=>'nullable|string',
            'incidencia'=>'required|integer|unique',
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

    public function messages():array
    {
        return
        [
            'fk_tipo_solicitud.required'=>'No se seleccionó el tipo de solicitud',
            'fk_tipo_solicitud.exists'=>'El tipo de solicitud no existe',
            'fk_despacho.required'=>'El despacho es obligatorio',
            'incidencia.required'=>'La incidencia es obligatoria',
            'detalles.*.fk_articulo.required'=>'No se ha seleccionado ningún artículo',
            'detalles.*.cantidad_solicitada.required'=>'La cantidad es obligatoria'


        ];
    }
}
