<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;
use function App\Helpers\getCabangId;

class CapacityRequest extends FormRequest
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
                    $cabangId = getCabangId(); // ambil cabang dari helper

                    $exists = \App\Models\Capacity::where('name', $value)
                        ->where('cabang_id', $cabangId)
                        ->exists();

                    if ($exists) {
                        $fail('Mohon maaf, kapasitas dengan nama ini sudah tersedia di cabang ini.');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama kapasitas wajib diisi.',
            'name.max' => 'Nama kapasitas maksimal 100 karakter.',
        ];
    }
}
