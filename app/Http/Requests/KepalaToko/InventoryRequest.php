<?php

namespace App\Http\Requests\KepalaToko;

use Illuminate\Foundation\Http\FormRequest;
use function App\Helpers\getCabangId;
use App\Models\Inventory;

class InventoryRequest extends FormRequest
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
            'code' => [
                'required',
                'max:100',
                function ($attribute, $value, $fail) {
                    $cabangId = getCabangId();

                    $exists = Inventory::where('code', $value)
                        ->where('cabang_id', $cabangId)
                        ->exists();

                    if ($exists) {
                        $fail('Mohon maaf, inventaris dengan kode ini sudah tersedia di cabang ini.');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Kode inventaris wajib diisi.',
            'code.max' => 'Kode inventaris maksimal 100 karakter.',
        ];
    }
}
