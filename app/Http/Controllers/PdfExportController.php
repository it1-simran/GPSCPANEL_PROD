<?php

namespace App\Http\Controllers;

use App\Template;
use PDF;
use Illuminate\Http\Request;

class PdfExportController extends Controller
{
    public function export()
    {
        $users = Template::where('is_deleted', 0)->get(); // Adjust query as needed
        $pdf = PDF::loadView('pdf.writers', compact('users')); // View to generate PDF

        return $pdf->download('templates.pdf');
    }
}
