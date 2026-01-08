<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // Tambahkan import ini

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        // Jika request adalah Ajax (dari DataTables)
        if ($request->ajax()) {
            $data = Category::query();

            return DataTables::of($data)
                ->addIndexColumn() // Untuk nomor urut (DT_RowIndex)
                ->editColumn('show_portal', function($row){
                    return $row->show_portal == 1 ? 'Active' : 'Non Active';
                })
                ->addColumn('action', function($row){
                    // Kita render view partial agar HTML popup sama persis & rapi
                    return view('pages.kepalatoko.kategori.action', compact('row'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.kepalatoko.kategori.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        Category::create([
            'category_name' => $request->category_name,
            'show_portal' => $request->show_portal,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'category_name' => $request->category_name,
            'show_portal' => $request->show_portal,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
