<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JigModel;

class JigController extends Controller
{
    //
    public function viewJig()
    {
        $jigs = JigModel::get();
        $url_type = self::getURLType();
        return view('view_jig', ['jigs' => $jigs, 'url_type' => $url_type]);
    }
    //
    public function delete($id)
    {
        $jig = JigModel::find($id);
        if (!$jig) {
            return redirect()->back()->withErrors(['error' => 'Jig not found.']);
        }
        $jig->delete();
        return redirect()->back()->with('success', 'Jig deleted successfully.');
    }
    //
    public function create(Request $request)
    {
        $request->validate([
            'jig_id'   => 'required|string|max:50',
            'imei'     => 'required|string|size:15',
        ]);
        $jigExists = JigModel::where('jigId', $request->jig_id)->first();
        if ($jigExists) {
            return redirect()->back()->withErrors(['jig_id' => 'Invalid Jig ID.']);
        }
        $imeiExists = JigModel::where('imei', $request->imei)->first();
        if ($imeiExists) {
            return redirect()->back()->withErrors(['imei' => 'IMEI already exists.']);
        }
        if ($request->filled('jig_id') && $request->filled('imei')) {
            $exists = JigModel::where('imei', $request->imei)->first();
            if ($exists) {
                $responses[] = [
                    'imei' => $request->imei,
                    'status' => 'error',
                    'message' => 'IMEI already exists'
                ];
            } else {
                JigModel::create([
                    'jigId' => $request->jig_id,
                    'imei'   => $request->imei,
                ]);
                return redirect()->back()->with('success', 'Jig added successfully.');
            }
        }
    }
    //
    public function update(Request $request, $id)
    {
        $jig = JigModel::findOrFail($id);

        $request->validate([
            'jig_id' => 'required|string|max:50',
            'imei'   => 'required|string|size:15|unique:jig,imei,' . $jig->id,
        ]);
        $jig->update([
            'jig_id' => $request->jig_id,
            'imei'   => $request->imei,
        ]);

        return redirect()->back()->with('success', 'Jig updated successfully.');
    }

}
