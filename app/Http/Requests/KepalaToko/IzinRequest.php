<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;

class IzinRequest extends FormRequest
{
    // public function authorize()
    // {
    //     return true;
    // }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'tipe' => 'required|string|in:izin,sakit,alfa',
            'tanggal' => 'required|date',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nominal_potongan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'dokumen' => 'nullable|file',
        ];
    }

}
