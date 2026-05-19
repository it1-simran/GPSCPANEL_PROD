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
            'version' => 'required|string|max:20|unique:version_control,version',
            'release_notes' => 'nullable|string',
        ], [
            'version.unique' => 'This version already exists.'
        ]);
        versionModel::create([
            'version' => $request->version,
            'release_notes' => $request->release_notes,
        ]);

        return redirect()->back()->with('success', 'Version updated successfully.');
    }

    public function destroy($id)
    {
        $version = versionModel::find($id);
        if (!$version) {
            return response()->json(['status' => 'error', 'message' => 'Version not found.']);
        }
        $version->delete();
        return response()->json(['status' => 'success', 'message' => 'Version deleted successfully.']);
    }
}
