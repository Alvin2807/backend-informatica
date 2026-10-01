<?php

namespace App\Http\Requests\Solicitud;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarDetalleRequest extends FormRequest
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
}
