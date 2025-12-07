<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Type;

class TypeRequest extends FormRequest
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
                // function ($attribute, $value, $fail) {
                //     $cabangId = getCabangId();

                //     $exists = Type::where('name', $value)
                //         ->where('cabang_id', $cabangId)
                //         ->exists();

                //     if ($exists) {
                //         $fail('Mohon maaf, jenis barang dengan nama ini sudah tersedia di cabang ini.');
                //     }
                // }
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama jenis barang wajib diisi.',
            'name.max' => 'Nama jenis barang maksimal 100 karakter.',
        ];
    }
}
