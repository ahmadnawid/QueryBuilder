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
 * SQL validation for safe query execution.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_querybuilder\local;

/**
 * Class for validating SQL queries for safe execution.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sql_validator {

    /**
     * Validates a SQL string for safe execution.
     *
     * @param string $sql The SQL string to validate.
     * @return string Error message if invalid, or empty string if valid.
     */
    public static function validate(string $sql): string {
        $sql = trim($sql);

        if (!preg_match('/^SELECT\s/i', $sql)) {
            return get_string('error_selectonly', 'report_querybuilder');
        }

        if (strpos($sql, ';') !== false) {
            return get_string('error_multistatement', 'report_querybuilder');
        }

        $blocked = ['UPDATE', 'DELETE', 'INSERT', 'DROP', 'ALTER', 'TRUNCATE'];
        foreach ($blocked as $word) {
            if (preg_match('/\b' . $word . '\b/i', $sql)) {
                return get_string('error_dangerous_keyword', 'report_querybuilder') . $word;
            }
        }

        return '';
    }

    /**
     * Legacy boolean check for safe SELECT queries.
     *
     * @param string $sql The SQL string to check.
     * @return bool True if safe, false otherwise.
     */
    public static function is_safe_select(string $sql): bool {
        return self::validate($sql) === '';
    }
}
