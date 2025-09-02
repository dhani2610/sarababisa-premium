<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StoreSetting;
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

        $data['toko_setting'] = StoreSetting::find(1);
        $data['pelanggan'] = Customer::count();

        return view('portal.index', $data);
    }
}
