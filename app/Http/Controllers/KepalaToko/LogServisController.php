<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogServisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Activity::where('subject_type', 'App\Models\ServiceTransaction')
            ->with('subject')
            ->orderBy('created_at', 'desc');

        // filter nomor servis
        if ($request->filled('nomor_servis')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('nomor_servis', 'like', '%' . $request->nomor_servis . '%');
            });
        }

        $activities = $query->paginate(10)->appends($request->all());

        return view('pages/kepalatoko/log-servis', compact('activities'));
    }


    public function destroy($model)
    {
        Activity::where('subject_type', $model)->truncate();

        toast('Semua log aktivitas servis berhasil dihapus.', 'success');

        return redirect()->route('log-servis');
    }
}
