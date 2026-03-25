<?php

$headings=['क्रमांक', 'उत्पाद का नाम', 'मूल्य', 'मात्रा'];
// Hindi data rows
$data = [
    [1, 'चाय', '₹120', 10],
    [2, 'कॉफी', '₹150', 5],
    [3, 'दूध', '₹60', 20],
    [4, 'शहद', '₹200', 2]
];
$filename='mangal-singh';
writeCSV($filename, $headings, $data);
 
 

function writeCSV($filename, $headings, $data) {   

    //Use tab as field separator
    $newTab  = "\t";
    $newLine  = "\n";

    $fputcsv  =  count($headings) ? '"'. implode('"'.$newTab.'"', $headings).'"'.$newLine : '';

    // Loop over the * to export
    if (! empty($data)) {
      foreach($data as $item) {
        $fputcsv .= '"'. implode('"'.$newTab.'"', $item).'"'.$newLine;
      }
    }

    //Convert CSV to UTF-16
    $encoded_csv = mb_convert_encoding($fputcsv, 'UTF-16LE', 'UTF-8');

    // Output CSV-specific headers
    header('Set-Cookie: fileDownload=true; path=/'); //This cookie is needed in order to trigger the success window.
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$filename.csv\";" );
    header("Content-Transfer-Encoding: binary");
    header('Content-Length: '. strlen($encoded_csv));
    echo chr(255) . chr(254) . $encoded_csv; //php array convert to csv/excel

    exit;
}
?>
