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
        $request->validate([
            'title' => 'required|string|max:255',
            'foto'  => 'required|image|mimes:png,jpg,jpeg,webp|max:1024',
        ], [
            'title.required' => 'Judul wajib diisi.',
            'title.string'   => 'Judul harus berupa teks.',
            'title.max'      => 'Judul maksimal 255 karakter.',
            'foto.required'  => 'Foto produk wajib diunggah.',
            'foto.image'     => 'File yang diunggah harus berupa gambar.',
            'foto.mimes'     => 'Format foto harus PNG, JPG, JPEG, atau WEBP.',
            'foto.max'       => 'Ukuran foto maksimal 1 MB.',
            'foto.uploaded'       => 'Upload foto gagal. Pastikan ukuran tidak lebih dari 1 MB.',
        ]);

        try {
            $path = $request->file('foto')->store('galleries', 'public');

            Gallery::create([
                'title' => $request->title,
                'foto'  => $path,
            ]);

            return redirect()->route('master-gallery.index')->with('success', 'Foto berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->route('master-gallery.index')->with('failed', 'Foto gagal ditambahkan');
        }
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);

        if (!empty($selectedIds)) {
            $galleries = Gallery::whereIn('id', $selectedIds)->get();

            foreach ($galleries as $gallery) {
                if ($gallery->foto && file_exists(storage_path('app/public/' . $gallery->foto))) {
                    unlink(storage_path('app/public/' . $gallery->foto));
                }
                $gallery->delete();
            }

            return response()->json(['message' => 'Foto berhasil dihapus.']);
        }

        return response()->json(['message' => 'Tidak ada foto yang dipilih.'], 400);
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
