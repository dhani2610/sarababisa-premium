<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;

class IzinRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'tipe' => 'required|in:izin,sakit,alfa',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
            'nominal_potongan' => 'required',
        ];
    }
}
