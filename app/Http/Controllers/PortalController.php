<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $data['productCategory'] = Category::orderBy('created_at', 'asc')->get();

        $query = Product::orderBy('created_at', 'desc');

        if ($request->has('category') && $request->category != '') {
            $query->where('categories_id', $request->category);
        }

        $data['products'] = $query->paginate(6); // pagination
        $data['total_products'] = Product::orderBy('created_at', 'desc')->get(); // pagination

        $data['toko_setting'] = StoreSetting::find(1);
        $data['kepala_toko_setting'] = User::find(1);
        $data['pelanggan'] = Customer::count();
        $data['galleries'] = Gallery::orderBy('created_at', 'desc')->get(); // pagination
        // dd($data['gallery']);

        return view('portal.index', $data);
    }
}
