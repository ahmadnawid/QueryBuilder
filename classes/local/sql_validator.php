<?php
namespace report_querybuilder\local;

defined('MOODLE_INTERNAL') || die();

class sql_validator {

    /**
     * Validates a SQL string for safe execution.
     * Returns an error string if invalid, or empty string if OK.
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
     * Legacy boolean check (kept for backwards compat).
     */
    public static function is_safe_select(string $sql): bool {
        return self::validate($sql) === '';
    }
}
