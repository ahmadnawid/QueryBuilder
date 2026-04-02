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
 * Database upgrade steps for Query Builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_report_querybuilder_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026030500) { // Use your next version number
        $table = new xmldb_table('report_querybuilder_queries');
        $field = new xmldb_field('category', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'name');
        if (!$dbman->field_exists($table, 'category')) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026030500, 'report', 'querybuilder');
    }

    if ($oldversion < 2026031200) {
        // No DB changes in this version — just registering new external services.
        upgrade_plugin_savepoint(true, 2026031200, 'report', 'querybuilder');
    }

    return true;

}
