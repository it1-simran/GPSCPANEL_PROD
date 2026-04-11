<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $reader = new Csv();
    // Assuming CSV uses commas, which is default
    $spreadsheet = $reader->load('Live_Tracking_TestCases.csv');

    $writer = new Xlsx($spreadsheet);
    $writer->save('Live_Tracking_TestCases.xlsx');
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
