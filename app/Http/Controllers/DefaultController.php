<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\ModelSerie;
use App\Models\Product;

class DefaultController extends Controller
{
    public function GetModelSerie(Request $request)
    {
        $brands_id = $request->brands_id;
        $allModelSerie = ModelSerie::select('id', 'name')
            ->where('cabang_id', getCabangId())
            ->where('brands_id', $brands_id)
            ->get();
        return response()->json($allModelSerie);
    } // End Method

    public function GetProduct(Request $request)
    {
        $categories_id = $request->categories_id;
        $allProduct = Product::where('cabang_id', getCabangId())
            ->where('categories_id', $categories_id)
            ->get();
        return response()->json($allProduct);
    } // End Method

    public function GetCustomers(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $query = Customer::select('id', 'nama', 'nomor_hp')
            ->where('cabang_id', getCabangId());

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nomor_hp', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->orderBy('id', 'desc')->limit(30)->get();

        $results = $customers->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->nama . ' (' . $item->nomor_hp . ')',
            ];
        });

        return response()->json(['results' => $results]);
    }
}
