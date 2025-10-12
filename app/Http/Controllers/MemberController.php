<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:products.view')->only(['index','show']);
    $this->middleware('permission:products.create')->only(['create','store']);
    $this->middleware('permission:products.update')->only(['edit','update']);
    $this->middleware('permission:products.delete')->only(['destroy']);
}

    public function index()
    {
        $members = Member::latest()->get();

        return Inertia::render('Members/Index', [
            'members' => $members
        ]);
    }

    public function create()
    {
        return Inertia::render('Members/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'phone' => 'required',
            // validasi lain sesuai kebutuhan
        ]);

        Member::create($request->all());

        return redirect()->route('members.index')->with('message', 'Member berhasil ditambahkan');
    }

    public function edit(Member $member)
    {
        return Inertia::render('Members/Edit', [
            'member' => $member
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $member->update($request->all());

        return redirect()->route('members.index')->with('message', 'Member berhasil diupdate');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('message', 'Member berhasil dihapus');
    }
}
