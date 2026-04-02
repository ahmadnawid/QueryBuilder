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
 * Query manager for saved queries CRUD operations.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_querybuilder\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for managing CRUD operations on saved queries.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class query_manager {

    /**
     * Get all saved queries.
     *
     * @return array List of query records.
     */
    public static function get_all() {
        global $DB;
        return $DB->get_records('report_querybuilder_queries', null, 'name ASC');
    }

    /**
     * Get a single saved query by ID.
     *
     * @param int $id Query ID.
     * @return object|false Query record or false if not found.
     */
    public static function get($id) {
        global $DB;
        return $DB->get_record('report_querybuilder_queries', ['id' => $id]);
    }

    /**
     * Delete a saved query by ID.
     *
     * @param int $id Query ID.
     * @return bool True on success, false otherwise.
     */
    public static function delete($id) {
        global $DB;
        return $DB->delete_records('report_querybuilder_queries', ['id' => $id]);
    }

    /**
     * Update a saved query.
     *
     * @param int $id Query ID.
     * @param string $name Query name.
     * @param string $sql SQL text.
     * @param string|null $category Query category.
     * @return bool True on success, false otherwise.
     */
    public static function update($id, $name, $sql, $category = null) {
        global $DB;
        $record = $DB->get_record('report_querybuilder_queries', ['id' => $id]);
        if (!$record) {
            return false;
        }
        $record->name         = $name;
        $record->querytext    = $sql;
	$record->category     = $category;
        $record->timemodified = time();
        return $DB->update_record('report_querybuilder_queries', $record);
    }

     /**
     * Insert a new saved query.
     *
     * @param string $name Query name.
     * @param string $sql SQL text.
     * @param string|null $category Query category.
     * @return int Inserted record ID.
     */
    public static function insert($name, $sql, $category = null) {
        global $DB;
        $record = (object)[
            'name'         => $name,
            'querytext'    => $sql,
	    'category'	   => $category,
            'timecreated'  => time(),
            'timemodified' => time()
        ];
        return $DB->insert_record('report_querybuilder_queries', $record);
    }
}

