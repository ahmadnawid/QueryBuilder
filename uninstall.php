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
 * Uninstall script for report_querybuilder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom uninstallation procedure.
 *
 * @return bool true on success
 */
function xmldb_report_querybuilder_uninstall() {
    global $DB, $CFG;

    require_once($CFG->libdir . '/ddllib.php');

    $dbman = $DB->get_manager();

    // Drop the saved queries table if it exists.
    $table = new xmldb_table('report_querybuilder_queries');
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }

    return true;
}
