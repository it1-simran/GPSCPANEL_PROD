<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\versionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class versionController extends Controller
{
    public function index()
    {
        $version = DB::table('version_control')->get();
        return view('version_control', ['version' => $version]);
    }
    public function submitVersion(Request $request)
    {
        $request->validate([
            'version' => 'required|string|max:20',
            'release_notes' => 'nullable|string',
        ]);
        versionModel::create([
            'version' => $request->version,
            'release_notes' => $request->release_notes,
        ]);

        return redirect()->back()->with('success', 'Version updated successfully.');
    }
}
