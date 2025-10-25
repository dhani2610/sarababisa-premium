<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize()
    {
        // atur kebijakan jika perlu, sementara allow all
        return true;
    }

    public function rules()
    {
        return [
            'servis_transaction_id' => 'required|exists:service_transactions,id',
            'nominal' => 'required|numeric|min:0',
            // period will be input type month like "2025-10" -> convert in controller
            'period' => 'nullable|date_format:Y-m',
        ];
    }

    public function messages()
    {
        return [
            'servis_transaction_id.required' => 'Nomor servis harus diisi.',
            'servis_transaction_id.exists' => 'Nomor servis tidak valid.',
            'nominal.required' => 'Nominal harus diisi.',
            'nominal.numeric' => 'Nominal harus angka.',
            'period.date_format' => 'Format bulan/tahun harus yyyy-mm (input type="month").',
        ];
    }
}
