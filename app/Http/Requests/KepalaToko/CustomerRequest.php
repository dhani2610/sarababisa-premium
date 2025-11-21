<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;
use function App\Helpers\getCabangId;

class CustomerRequest extends FormRequest
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
            'nama' => 'required|max:100',
            'kategori' => 'required|max:100',
            'alamat' => 'required|max:100',
            'nomor_hp' => [
                'required',
                function ($attribute, $value, $fail) {
                    $cabangId = getCabangId();

                    $exists = \App\Models\Customer::where('nomor_hp', $value)
                        ->where('cabang_id', $cabangId)
                        ->exists();

                    if ($exists) {
                        $fail('Mohon maaf, pelanggan dengan nomor HP ini sudah tersedia di cabang ini.');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama pelanggan wajib diisi.',
            'nama.max' => 'Nama pelanggan maksimal 100 karakter.',
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.max' => 'Kategori maksimal 100 karakter.',
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.max' => 'Alamat maksimal 100 karakter.',
        ];
    }
}
