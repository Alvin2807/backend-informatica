<?php

namespace App\Http\Requests\SolicitudEntrada;

use Illuminate\Foundation\Http\FormRequest;

class EditarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fk_tipo_solicitud' => [
                'required',
                'integer',
                'exists:inv_tipo_solicitud,id_tipo_solicitud',
            ],

            'fk_despacho' => [
                'required',
                'integer',
                'exists:inv_despachos,id_despacho',
            ],

            'num_solicitud' => [
                'required',
                'string',
                'max:100',
            ],

            'entregado_por' => [
                'required',
                'string',
                'max:100',
            ],

            'recibido_por' => [
                'required',
                'string',
                'max:100',
            ],

            'fecha_solicitud' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fk_tipo_solicitud.required' =>
                'El tipo de solicitud es obligatorio.',

            'fk_tipo_solicitud.integer' =>
                'El tipo de solicitud debe ser un número.',

            'fk_tipo_solicitud.exists' =>
                'El tipo de solicitud no existe.',

            'fk_despacho.required' =>
                'El despacho es obligatorio.',

            'fk_despacho.integer' =>
                'El despacho debe ser un número.',

            'fk_despacho.exists' =>
                'El despacho no existe.',

            'num_solicitud.required' =>
                'El número de solicitud es obligatorio.',

            'num_solicitud.string' =>
                'El número de solicitud debe ser texto.',

            'num_solicitud.max' =>
                'El número de solicitud no puede superar los 100 caracteres.',

            'entregado_por.required' =>
                'El campo entregado por es obligatorio.',

            'entregado_por.string' =>
                'El campo entregado por debe ser texto.',

            'entregado_por.max' =>
                'El campo entregado por no puede superar los 100 caracteres.',

            'recibido_por.required' =>
                'El campo recibido por es obligatorio.',

            'recibido_por.string' =>
                'El campo recibido por debe ser texto.',

            'recibido_por.max' =>
                'El campo recibido por no puede superar los 100 caracteres.',

            'fecha_solicitud.required' =>
                'La fecha de solicitud es obligatoria.',

            'fecha_solicitud.date' =>
                'La fecha de solicitud no es válida.',
        ];
    }
}
