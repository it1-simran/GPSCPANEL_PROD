<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImeiModel;
use App\Imports\ImeiImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImeiController extends Controller
{
    public function viewImei(Request $request)
    {
        $imeis = ImeiModel::get();
        $url_type = self::getURLType();
        return view('view_uploaded_imei', ['imeis' => $imeis, 'url_type' => $url_type]);
    }
    public function uploadImei(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,xlsx,xls,txt|max:2048',
        ]);
        $file = $request->file('csv_file');
        $rows = Excel::toArray(new ImeiImport, $file);
        
        if (empty($rows) || empty($rows[0])) {
            return redirect()->back()->with('error', 'The uploaded file is empty.');
        }

        $data = $rows[0];
        $headerRow = array_map(function($val) { return strtolower(trim((string)$val)); }, $data[0]);
        
        $imeiIndex = 0;
        $hasHeader = false;

        foreach ($headerRow as $index => $header) {
            if ($header === 'imei' || $header === 'imei number') {
                $imeiIndex = $index;
                $hasHeader = true;
                break;
            }
        }

        if ($hasHeader || in_array('sr. no.', $headerRow) || in_array('created at', $headerRow)) {
            unset($data[0]);
        }
        
        $validCount = 0;
        $invalidCount = 0;
        $duplicateCount = 0;

        foreach ($data as $value) {
            if (!isset($value[$imeiIndex])) continue;
            
            $imeiValue = trim((string)$value[$imeiIndex]);
            if (empty($imeiValue)) continue;

            // Strict validation: IMEIs must be numeric and maximum 15 digits
            if (!preg_match('/^[0-9]{1,15}$/', $imeiValue)) {
                $invalidCount++;
                continue;
            }

            $checkImeiExist = ImeiModel::where('imei', $imeiValue)->first();
            if (!$checkImeiExist) {
                ImeiModel::create(['imei' => $imeiValue]);
                $validCount++;
            } else {
                $duplicateCount++;
            }
        }

        if ($validCount > 0) {
            $msg = "$validCount valid IMEI(s) imported successfully.";
            if ($invalidCount > 0) $msg .= " Skipped $invalidCount invalid entries.";
            if ($duplicateCount > 0) $msg .= " Skipped $duplicateCount duplicates.";
            return redirect()->back()->with('success', $msg);
        } else {
            $msg = "No new valid IMEIs were imported.";
            if ($invalidCount > 0) $msg .= " Found $invalidCount invalid entries.";
            if ($duplicateCount > 0) $msg .= " Found $duplicateCount duplicates.";
            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Remove an uploaded IMEI row from the `imeis` table (not eSIM customers).
     */
    public function destroy(Request $request, $id)
    {
        $imei = ImeiModel::find($id);
        if (!$imei) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'IMEI record not found.']);
            }
            return redirect()->back()->with('error', 'IMEI record not found.');
        }
        $label = $imei->imei;
        $imei->delete();

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'IMEI ' . $label . ' was deleted successfully.']);
        }
        return redirect()->back()->with('success', 'IMEI ' . $label . ' was deleted successfully.');
    }
    public function multiDelete(Request $request)
    {
        $ids = $request->ids;
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No items selected.']);
        }
        ImeiModel::whereIn('id', $ids)->delete();
        return response()->json(['status' => 'success', 'message' => 'Selected items have been deleted.']);
    }
}
