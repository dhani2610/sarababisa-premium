<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ServiceAction;

class ServiceActionRequest extends FormRequest
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
            'nama_tindakan' => [
                'required',
                function ($attribute, $value, $fail) {
                    $cabangId = getCabangId();

                    $exists = ServiceAction::where('nama_tindakan', $value)
                        ->where('cabang_id', $cabangId)
                        ->exists();

                    if ($exists) {
                        $fail('Mohon maaf, tindakan servis dengan nama ini sudah tersedia di cabang ini.');
                    }
                }
            ],
            'modal_sparepart' => 'required',
            'harga_toko' => 'required',
            'harga_pelanggan' => 'required',
            'garansi' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nama_tindakan.required' => 'Nama tindakan wajib diisi.',
            'modal_sparepart.required' => 'Modal sparepart wajib diisi.',
            'harga_toko.required' => 'Harga toko wajib diisi.',
            'harga_pelanggan.required' => 'Harga pelanggan wajib diisi.',
            'garansi.required' => 'Garansi wajib diisi.',
        ];
    }
}
