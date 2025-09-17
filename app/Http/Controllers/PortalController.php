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
        $data['productCategory'] = Category::where('show_portal', 1)
            ->orderBy('created_at', 'asc')
            ->get();

        $query = Product::where('is_portal',1)->whereIn('categories_id', $data['productCategory']->pluck('id'))
            ->orderBy('created_at', 'desc');

        // Filter Category
        if ($request->has('category') && $request->category != '') {
            $query->where('categories_id', $request->category);
        }

        // Search Produk
        if ($request->has('search') && $request->search != '') {
            $query->where('product_name', 'LIKE', '%' . $request->search . '%');
        }

        // Pilihan Show (default 3)
        $perPage = $request->input('show', 3);
        $data['products'] = $query->paginate($perPage)->appends($request->all());

        $data['total_products'] = Product::where('is_portal',1)->whereIn('categories_id', $data['productCategory']->pluck('id'))->count();
        $data['toko_setting'] = StoreSetting::find(1);
        $data['kepala_toko_setting'] = User::find(1);
        $data['pelanggan'] = Customer::count();
        $data['galleries'] = Gallery::orderBy('created_at', 'desc')->get();

        return view('portal.index', $data);
    }
    public function installAppIOS(Request $request)
    {
        
          $data['productCategory'] = Category::where('show_portal', 1)
            ->orderBy('created_at', 'asc')
            ->get();

        $query = Product::where('is_portal',1)->whereIn('categories_id', $data['productCategory']->pluck('id'))
            ->orderBy('created_at', 'desc');

        // Filter Category
        if ($request->has('category') && $request->category != '') {
            $query->where('categories_id', $request->category);
        }

        // Search Produk
        if ($request->has('search') && $request->search != '') {
            $query->where('product_name', 'LIKE', '%' . $request->search . '%');
        }

        // Pilihan Show (default 3)
        $perPage = $request->input('show', 3);
        $data['products'] = $query->paginate($perPage)->appends($request->all());

        $data['total_products'] = Product::where('is_portal',1)->whereIn('categories_id', $data['productCategory']->pluck('id'))->count();
        $data['toko_setting'] = StoreSetting::find(1);
        $data['kepala_toko_setting'] = User::find(1);
        $data['pelanggan'] = Customer::count();
        $data['galleries'] = Gallery::orderBy('created_at', 'desc')->get();

        return view('portal.install-app-ios',$data);
    }
}
