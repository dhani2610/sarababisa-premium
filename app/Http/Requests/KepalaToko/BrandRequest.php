<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required', 
                'max:100',
                function ($attribute, $value, $fail) {
                    $existing = \App\Models\Brand::withTrashed()
                        ->where('name', $value)
                        ->first();

                    if ($existing && $existing->deleted_at === null) {
                        $fail('Mohon maaf, merek dengan nama ini sudah tersedia.');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Mohon maaf, inputan tidak dapat diproses karena merek dengan nama ini sudah tersedia.',
        ];
    }
}
