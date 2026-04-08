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
 * SQL utility functions for the Query Builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_querybuilder\local;

/**
 * SQL utility functions for the Query Builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sql_utils {

    /**
     * Remove trailing semicolon and whitespace.
     *
     * @param string $sql The SQL string to clean.
     * @return string Cleaned SQL string.
     */
    public static function clean_sql(string $sql): string {
        return rtrim($sql, " \t\n\r\0\x0B;");
    }

    /**
     * Detect if SQL already contains LIMIT or OFFSET.
     *
     * @param string $sql The SQL string to check.
     * @return bool True if LIMIT or OFFSET is present, false otherwise.
     */
    public static function has_limit_or_offset(string $sql): bool {
        $lower = strtolower($sql);
        $lower = preg_replace('/\s+/', ' ', $lower);
        return preg_match('/\blimit\b/', $lower) ||
               preg_match('/\boffset\b/', $lower);
    }

    /**
     * Parse a SQL string back into builder form state.
     * Returns an array with keys: base, fields, joins, filter_field, filter_op, filter_value.
     * Returns null if the SQL is too complex to parse.
     *
     * @param string $sql The SQL string to parse.
     * @return array|null The builder state array or null if not parseable.
     */
    public static function parse_sql_to_builder_state($sql) {
        global $CFG;
        require_once($CFG->dirroot . '/report/querybuilder/classes/domain/entities.php');
        $entities = \report_querybuilder\domain\entities::list();

        // Build a table name to entity key map.
        $tabletokey = [];
        foreach ($entities as $key => $entity) {
            if (!empty($entity['table'])) {
                $tabletokey[$entity['table']] = $key;
            }
        }

        $state = [];

        // Parse base table (FROM {tablename} [alias]).
        if (preg_match('/FROM\s+\{(\w+)\}(?:\s+\w+)?/i', $sql, $matches)) {
            $tablename = $matches[1];
            $state['base'] = isset($tabletokey[$tablename]) ? $tabletokey[$tablename] : $tablename;
        }

        // Parse selected fields (SELECT ...).
        if (preg_match('/SELECT\s+(.*?)\s+FROM/i', $sql, $matches)) {
            $fields = explode(',', $matches[1]);
            $state['fields'] = array_map(function($f) {
                $f = trim($f);
                if (strpos($f, '.') !== false) {
                    $parts = explode('.', $f);
                    return end($parts);
                }
                return $f;
            }, $fields);
        }

        // Parse WHERE clause for a simple filter (optional).
        if (preg_match('/WHERE\s+(.*?)\s*(LIMIT|$)/is', $sql, $matches)) {
            $where = $matches[1];
            if (preg_match('/(\w+\.\w+)\s*([=<>]+)\s*(.+)/', $where, $w)) {
                $state['filter_field'] = $w[1];
                $state['filter_op'] = $w[2];
                $state['filter_value'] = trim($w[3], "' ");
            }
        }

        // Parse joins (JOIN {tablename} [alias]).
        if (!empty($state['base']) && isset($entities[$state['base']]['joins'])) {
            $joinsdef = $entities[$state['base']]['joins'];
            if (preg_match_all('/JOIN\s+\{(\w+)\}(?:\s+\w+)?/i', $sql, $matches)) {
                $tablejoins = $matches[1];
                $joinkeys = [];
                foreach ($tablejoins as $table) {
                    foreach ($joinsdef as $key => $join) {
                        if ($join['table'] === $table) {
                            $joinkeys[] = $key;
                            break;
                        }
                    }
                }
                $state['joins'] = $joinkeys;
            }
        }

        if (!empty($state['base']) && !empty($state['fields'])) {
            return $state;
        }
        return null;
    }
}
