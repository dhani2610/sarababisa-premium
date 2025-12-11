<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;

class PortalController extends Controller
{
public function indexHome(Request $request)
{
    // Ambil data cabang untuk ditampilkan di kartu
    $data['cabang'] = Cabang::orderBy('created_at', 'asc')->get();
    $data['kepala_toko_setting'] = User::where('cabang_id', 1)->where('role', 'Kepala Toko')->first() ?? User::first(); 
    
    // Return ke view baru khusus pemilihan cabang
    return view('portal.home', $data);
}

public function index(Request $request, $id)
{
    // 1. Validasi Cabang
    $cabang = Cabang::findOrFail($id);
    $data['current_cabang'] = $cabang;

    // 2. Filter Kategori (Mungkin perlu filter by cabang jika kategori spesifik per cabang)
    $data['productCategory'] = Category::where('show_portal', 1)
        ->orderBy('created_at', 'asc')
        ->get();

    // 3. Query Produk (Filter by Cabang ID)
    $query = Product::where('is_portal', 1)
        ->where('cabang_id', $id) // <--- FILTER UTAMA
        ->whereIn('categories_id', $data['productCategory']->pluck('id'))
        ->orderBy('created_at', 'desc');

    // Filter Kategori (Input User)
    if ($request->has('category') && $request->category != '') {
        $query->where('categories_id', $request->category);
    }

    // Search Produk
    if ($request->has('search') && $request->search != '') {
        $query->where('product_name', 'LIKE', '%' . $request->search . '%');
    }

    // Pagination
    $perPage = $request->input('show', 3);
    $data['products'] = $query->paginate($perPage)->appends($request->all());

    // 4. Data Pendukung (Disesuaikan dengan Cabang ID)
    $data['total_products'] = Product::where('is_portal', 1)
        ->where('cabang_id', $id)
        ->whereIn('categories_id', $data['productCategory']->pluck('id'))
        ->count();
        
    // Asumsi StoreSetting & User ada kolom cabang_id. 
    // Jika tidak, sesuaikan logic ini.
    $data['toko_setting'] = StoreSetting::where('cabang_id', $id)->first() ?? StoreSetting::first();
    $data['kepala_toko_setting'] = User::where('cabang_id', $id)->where('role', 'Kepala Toko')->first() ?? User::first(); 
    
    // if (getCabangId() == 1) {
    //         $users = User::where('cabang_id',getCabangId())->where('role','Kepala Toko')->orderBy('id','asc')->first();
    //     }else{
    //         $users = User::where('cabang_id',getCabangId())->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
    //     }

    $data['pelanggan'] = Customer::where('cabang_id', $id)->count();
    $data['galleries'] = Gallery::where('cabang_id', $id)->orderBy('created_at', 'desc')->get();

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
