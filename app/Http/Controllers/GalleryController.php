<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.master.gallery');
    }

    public function store(Request $request)
    {
        try {
            $path = $request->file('foto')->store('galleries', 'public');
    
            Gallery::create([
                'title' => $request->title,
                'foto' => $path,
            ]);
    
            return redirect()->route('master-gallery.index')->with('success', 'Foto berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->route('master-gallery.index')->with('failed', 'Foto gagal ditambahkan');
        }
    }

    public function destroy($id)
    {
        $item = Gallery::findOrFail($id);
        if ($item->foto && file_exists(storage_path('app/public/' . $item->foto))) {
            unlink(storage_path('app/public/' . $item->foto));
        }
        $item->delete();

        return redirect()->route('master-gallery.index')->with('success', 'Foto berhasil dihapus');
    }
}
