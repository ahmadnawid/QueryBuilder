<?php
require('../../config.php');
require_login();

$type = required_param('type', PARAM_ALPHA);
$sql  = urldecode(required_param('sql', PARAM_RAW));

global $DB, $CFG;

if ($type === 'csv') {
    export_csv($sql);
} else if ($type === 'pdf') {
    export_pdf($sql);
}

function export_csv($sql) {
    global $DB;

    $results = $DB->get_records_sql($sql);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="query_export.csv"');

    $out = fopen('php://output', 'w');

    if ($results) {
        $first = reset($results);
        fputcsv($out, array_keys((array)$first));

        foreach ($results as $row) {
            fputcsv($out, (array)$row);
        }
    }

    fclose($out);
    exit;
}

function export_pdf($sql) {
    global $DB, $CFG;

    require_once($CFG->libdir . '/pdflib.php');

    $results = $DB->get_records_sql($sql);

    $pdf = new pdf();
    $pdf->SetTitle('SQL Query Export');
    $pdf->AddPage();

    $html = '<h2>SQL Query Export</h2>';

    if ($results) {
        $first = reset($results);
        $columns = array_keys((array)$first);

        $html .= '<table border="1" cellpadding="4"><tr>';
        foreach ($columns as $col) {
            $html .= '<th>' . s($col) . '</th>';
        }
        $html .= '</tr>';

        foreach ($results as $row) {
            $html .= '<tr>';
            //foreach ((array)$row as $value) {
              //  $html .= '<td>' . s($value) . '</td>';
            //}
	    $row = (array)$row;
	    foreach ($columns as $col) {
    	    $value = isset($row[$col]) ? $row[$col] : '';
    	    $html .= '<td>' . s($value) . '</td>';
	}

            $html .= '</tr>';
        }

        $html .= '</table>';
    }

    $pdf->writeHTML($html);
    $pdf->Output('query_export.pdf', 'D');
    exit;
}

