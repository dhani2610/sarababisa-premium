<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ModelSerie;
use App\Models\Product;

class DefaultController extends Controller
{
    public function GetModelSerie(Request $request)
    {
        $brands_id = $request->brands_id;
        $allModelSerie = ModelSerie::where('cabang_id',getCabangId())->where('brands_id', $brands_id)->get();
        return response()->json($allModelSerie);
    } // End Mehtod

    public function GetProduct(Request $request)
    {
        $categories_id = $request->categories_id;
        $allProduct = Product::where('cabang_id',getCabangId())->where('categories_id', $categories_id)->get();
        return response()->json($allProduct);
    } // End Mehtod








}
