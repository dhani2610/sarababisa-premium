<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required|max:100',
            'username' => 'required|max:100|unique:users,username',
            'role' => 'max:50',
            'types_id' => 'exists:types,id|nullable',
            'nik' => 'max:100',
            'nomor_hp' => 'max:50',
            'alamat' => 'max:255',
            'persen' => 'max:50',
            'exp_date' => 'max:100',
            'workers_id' => 'exists:workers,id|nullable',
            'persen_investor' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'username.unique' => 'Mohon maaf, inputan tidak dapat diproses karena akun dengan nama pengguna ini sudah tersedia.',
        ];
    }
}
