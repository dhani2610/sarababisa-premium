<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStokRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'dari_cabang_id' => 'required|integer',
            'ke_cabang_id' => 'required|integer|different:dari_cabang_id',
            'dari_produk_id' => 'required|exists:products,id',
            'ke_produk_id' => 'nullable|exists:products,id',
            'stok' => 'required|integer|min:1',
            'tanggal' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'ke_cabang_id.different' => 'Cabang tujuan tidak boleh sama dengan cabang asal.',
            'stok.min' => 'Jumlah stok minimal 1.',
        ];
    }
}
