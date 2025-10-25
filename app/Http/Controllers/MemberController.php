<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::all();
        return view('members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'link'  => 'required|url',
        ]);

        Member::create($request->only('title', 'link'));

        return response()->json(['msg' => 'Member berhasil ditambahkan']);
    }

    public function update(Request $request, Member $member)
    {
        $request->validate([
            'title' => 'required',
            'link'  => 'required|url',
        ]);

        $member->update($request->only('title', 'link'));

        return response()->json(['msg' => 'Member berhasil diupdate']);
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return response()->json(['msg' => 'Member berhasil dihapus']);
    }

    // fungsi update expired remote
    public function updateExpired(Request $request, Member $member)
    {
        $request->validate([
            'exp_date' => 'required|date',
        ]);

        try {
            $response = Http::get($member->link . '/update-expired', [
                'exp_date' => $request->exp_date,
            ]);

            return response()->json(['msg' => 'Update Expired sukses', 'response' => $response->json()]);
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Gagal update expired', 'error' => $th->getMessage()]);
        }
    }
}
