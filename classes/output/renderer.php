<?php
namespace report_querybuilder\output;

global $CFG;
require_once($CFG->libdir.'/tablelib.php');

defined('MOODLE_INTERNAL') || die();

use html_table;
use moodle_url;
use html_writer;

class renderer extends \plugin_renderer_base {

    private function strip_order_by($sql) {
    return preg_replace('/ORDER\s+BY[\s\S]*$/i', '', $sql);
    }


    public function render_results_table($columns, $sql, $download = '') {
    global $DB, $PAGE;

    $table = new \flexible_table('querybuilder-results');

    // Define columns
    $table->define_columns($columns);
    $table->define_headers($columns);

    // Enable features
    $table->collapsible(true);
    $table->sortable(true);
    $table->pageable(true);
    $table->is_persistent(true);

    $table->is_downloadable(true);
    $table->show_download_buttons_at([TABLE_P_BOTTOM]);
    $download = optional_param('download', '', PARAM_ALPHA);
    $table->is_downloading($download, 'querybuilder_results', 'Query results');
     // IMPORTANT: base URL
    $table->define_baseurl(
        new \moodle_url('/report/querybuilder/index.php', [
            'advanced' => 1,
            'advsql' => $sql
        ])
    );

    // Count rows
    $cleansql = $this->strip_order_by($sql);
    $countsql = "SELECT COUNT(*) FROM ($cleansql) q";
    $total = $DB->count_records_sql($countsql);


    $table->pagesize(15, $total);
    $table->download_buttons();
    $table->set_attribute('class', 'generaltable generalbox querybuilder-table');
    $table->set_attribute('style', 'width:100%');
    $table->column_class('id', 'text-center');
    $table->column_class('email', 'text-left');
    // Setup table
    $table->setup();

    // Sorting support
    $sort = $table->get_sql_sort();

    if ($sort) {
        if (stripos($sql, 'ORDER BY') !== false) {
            $sql = preg_replace('/ORDER\s+BY[\s\S]*$/i', "ORDER BY $sort", $sql);
        } else {
            $sql .= " ORDER BY $sort";
        }
    }


     // Real pagination
    $limitfrom = $table->get_page_start();
    $limitnum  = $table->get_page_size();


    // Stream database results
    $recordset = $DB->get_recordset_sql($sql, null, $limitfrom, $limitnum);

    foreach ($recordset as $record) {
        $table->add_data(array_values((array)$record));
    }

    $recordset->close();

    $table->finish_output();
    return '';

}

    public function datatables_init_script() {
        return '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var table = document.getElementById("queryresults");
                if (table && typeof jQuery !== "undefined" && typeof jQuery.fn.DataTable === "function") {
                    jQuery(table).DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true
                    });
                }
            });
        </script>';
    }


public function export_buttons($sql) {
    $output = html_writer::link(
        new \moodle_url('/report/querybuilder/export.php', [
            'type' => 'csv',
            'sql'  => urlencode($sql),
        ]),
        get_string('btn_export_csv', 'report_querybuilder'),
        ['class' => 'btn btn-success', 'style' => 'margin-right:10px;']
    );
    $output .= html_writer::link(
        new \moodle_url('/report/querybuilder/export.php', [
            'type' => 'pdf',
            'sql'  => urlencode($sql),
        ]),
        get_string('btn_export_pdf', 'report_querybuilder'),
        ['class' => 'btn btn-warning']
    );
    return $output;
}

public function back_button() {
    return html_writer::tag(
        'p',
        html_writer::link(
            new \moodle_url('/report/querybuilder/index.php', ['advanced' => 1]),
            get_string('btn_back', 'report_querybuilder'),
            ['class' => 'btn btn-secondary']
        ),
        ['style' => 'margin-top:20px;']
    );
}

}
