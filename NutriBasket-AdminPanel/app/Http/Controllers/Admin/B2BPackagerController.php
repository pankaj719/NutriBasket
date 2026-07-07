<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\B2BPackager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class B2BPackagerController extends Controller
{
    public function index()
    {
        $packagers = B2BPackager::paginate(25);
        return view('admin-views.b2b-packagers.index', compact('packagers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:b2b_packagers,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);
        B2BPackager::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
        return redirect()->back()->with('success', 'B2B Packager added!');
    }

    public function update(Request $request, $id)
    {
        $packager = B2BPackager::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:b2b_packagers,email,' . $packager->id,
            'phone' => 'required|string|max:20',
        ]);
        $packager->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        return redirect()->back()->with('success', 'B2B Packager updated!');
    }

    public function destroy($id)
    {
        $packager = B2BPackager::findOrFail($id);
        $packager->delete();
        return redirect()->back()->with('success', 'B2B Packager deleted successfully!');
    }
}
