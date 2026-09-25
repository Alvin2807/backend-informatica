<?php

namespace App\Http\Requests\Articulos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RequestRegistrar extends FormRequest
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
            'fk_categoria' => 'required|integer|exists:inv_categorias,id_categoria',

            'fk_marca' => 'required|integer|exists:inv_marcas,id_marca',

            'fk_color' => 'nullable|integer|exists:inv_colores,id_color',

            'fk_modelo' => 'required|integer|exists:inv_modelos,id_modelo',

            'codigo' => 'required|string|max:20',

            'modelo_insumo' => 'required|string|max:100',

            'detalle' => 'nullable|string|max:255',


        ];
    }

}
