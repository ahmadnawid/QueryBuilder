<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Output renderer for Query Builder results.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_querybuilder\output;

use html_table;
use moodle_url;
use html_writer;

/**
 * Renderer for outputting Query Builder results and UI elements.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Removes ORDER BY clause from a SQL string.
     *
     * @param string $sql The SQL string.
     * @return string SQL string without ORDER BY.
     */
    private function strip_order_by($sql) {
        return preg_replace('/ORDER\s+BY[\s\S]*$/i', '', $sql);
    }

    /**
     * Renders the results table using Moodle's flexible_table.
     *
     * @param array $columns The column names.
     * @param string $sql The SQL query.
     * @param string $download Download type (csv, xls, etc.), or empty for normal display.
     * @return string Empty string (output is sent directly).
     */
    public function render_results_table($columns, $sql, $download = '') {
        global $DB;

	// Strip any existing LIMIT/OFFSET from the SQL.
        // flexible_table handles pagination by adding its own LIMIT/OFFSET.
        // Having two LIMIT clauses causes a PostgreSQL syntax error.
        $sql = preg_replace('/\s+LIMIT\s+\d+(\s+OFFSET\s+\d+)?\s*$/i', '', trim($sql));
        $table = new \flexible_table('querybuilder-results');

        // Define columns.
        $table->define_columns($columns);
        $table->define_headers($columns);

        // Enable features.
        $table->collapsible(true);
        $table->sortable(true);
        $table->pageable(true);
        $table->is_persistent(true);

        $table->is_downloadable(true);
        $table->show_download_buttons_at([TABLE_P_BOTTOM]);
        $download = optional_param('download', '', PARAM_ALPHA);
        $table->is_downloading($download, 'querybuilder_results', 'Query results');
        // IMPORTANT: base URL.
        $table->define_baseurl(
            new \moodle_url('/report/querybuilder/index.php', [
                'advsql' => $sql,
                'sesskey' => sesskey(),
            ])
        );

        // Count rows.
        $cleansql = $this->strip_order_by($sql);
        $countsql = "SELECT COUNT(*) FROM ($cleansql) q";
        $total = $DB->count_records_sql($countsql);

	if ($table->is_downloading()) {
            $table->pagesize($total, $total);
	} else {
    	    $table->pagesize(15, $total);
	}

        $table->download_buttons();
        $table->set_attribute('class', 'generaltable generalbox querybuilder-table');
        $table->set_attribute('style', 'width:100%');
        $table->column_class('id', 'text-center');
        $table->column_class('email', 'text-left');
        // Setup table.
        $table->setup();

        // Sorting support.
        $sort = $table->get_sql_sort();

        if ($sort) {
            if (stripos($sql, 'ORDER BY') !== false) {
                $sql = preg_replace('/ORDER\s+BY[\s\S]*$/i', "ORDER BY $sort", $sql);
            } else {
                $sql .= " ORDER BY $sort";
            }
        }

        // Real pagination.
        $limitfrom = $table->get_page_start();
        $limitnum  = $table->get_page_size();

        // Stream database results.
        $recordset = $DB->get_recordset_sql($sql, null, $limitfrom, $limitnum);

	foreach ($recordset as $record) {
    	    $row = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values((array)$record));
            $table->add_data($row);
        }

        $recordset->close();

        $table->finish_output();
        return '';
    }

    /**
     * Returns the JS snippet to initialize DataTables.
     *
     * @return string HTML/JS snippet.
     */
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

    /**
     * Returns export buttons for CSV and PDF.
     *
     * @param string $sql The SQL query.
     * @return string HTML for export buttons.
     */
    public function export_buttons($sql) {
        $output = html_writer::link(
            new \moodle_url('/report/querybuilder/export.php', [
                'type' => 'csv',
                'sql'  => urlencode($sql),
            ]),
            get_string('btn_export_csv', 'report_querybuilder'),
            ['class' => 'btn btn-success export-btn-csv']
        );
        $output .= html_writer::link(
            new \moodle_url('/report/querybuilder/export.php', [
                'type' => 'pdf',
                'sql'  => urlencode($sql),
            ]),
            get_string('btn_export_pdf', 'report_querybuilder'),
            ['class' => 'btn btn-warning export-btn-pdf']
        );
        return $output;
    }

    /**
     * Returns a back button to return to the main page.
     *
     * @return string HTML for the back button.
     */
    public function back_button() {
        return html_writer::tag(
            'p',
            html_writer::link(
                new \moodle_url('/report/querybuilder/index.php', ['advanced' => 1]),
                get_string('btn_back', 'report_querybuilder'),
                ['class' => 'btn btn-secondary back-btn']
            ),
            ['class' => 'back-btn-container']
        );
    }
}
