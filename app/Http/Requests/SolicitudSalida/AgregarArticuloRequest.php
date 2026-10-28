<?php

namespace App\Http\Requests\SolicitudSalida;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AgregarArticuloRequest extends FormRequest
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
            'fk_articulo'=>'required|integer|exists:inv_articulos,id_articulo',
            'cantidad_solicitada'=>'required|integer|min:1'
        ];
    }

    public function messages():array
    {
        return
        [
            'fk_articulo.required'=>'El artículo es obligatorio',
            'fk_articulo.exists'=>'El artículo ya existe',
            'cantidad_solicitada.required'=>'La cantidad solicitada es obligatoria'

        ];
    }
}
