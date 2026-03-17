<?php
namespace report_querybuilder\local;

defined('MOODLE_INTERNAL') || die();

class sql_utils {

    /**
     * Remove trailing semicolon and whitespace.
     */
    public static function clean_sql(string $sql): string {
        return rtrim($sql, " \t\n\r\0\x0B;");
    }

    /**
     * Detect if SQL already contains LIMIT or OFFSET.
     */
    public static function has_limit_or_offset(string $sql): bool {
        $lower = strtolower($sql);
        $lower = preg_replace('/\s+/', ' ', $lower);
        return preg_match('/\blimit\b/', $lower) ||
               preg_match('/\boffset\b/', $lower);
    }

    /**
     * Parse a SQL string back into builder form state.
     * Returns an array with keys: base, fields, joins, filter_field, filter_op, filter_value
     * Returns null if the SQL is too complex to parse.
     */
public static function parse_sql_to_builder_state($sql) {
    global $CFG;
    require_once($CFG->dirroot . '/report/querybuilder/classes/domain/entities.php');
    $entities = \report_querybuilder\domain\entities::list();

    // Build a table name to entity key map
    $tableToKey = [];
    foreach ($entities as $key => $entity) {
        if (!empty($entity['table'])) {
            $tableToKey[$entity['table']] = $key;
        }
    }

    $state = [];

    // Parse base table (FROM {tablename} [alias])
    if (preg_match('/FROM\s+\{(\w+)\}(?:\s+\w+)?/i', $sql, $matches)) {
        $tablename = $matches[1];
        $state['base'] = isset($tableToKey[$tablename]) ? $tableToKey[$tablename] : $tablename;
    }

    // Parse selected fields (SELECT ...)
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

    // Parse WHERE clause for a simple filter (optional)
    if (preg_match('/WHERE\s+(.*?)\s*(LIMIT|$)/is', $sql, $matches)) {
        $where = $matches[1];
        if (preg_match('/(\w+\.\w+)\s*([=<>]+)\s*(.+)/', $where, $w)) {
            $state['filter_field'] = $w[1];
            $state['filter_op'] = $w[2];
            $state['filter_value'] = trim($w[3], "' ");
        }
    }

    // Parse joins (JOIN {tablename} [alias])
    if (!empty($state['base']) && isset($entities[$state['base']]['joins'])) {
        $joinsdef = $entities[$state['base']]['joins'];
        if (preg_match_all('/JOIN\s+\{(\w+)\}(?:\s+\w+)?/i', $sql, $matches)) {
            $tableJoins = $matches[1];
            $joinKeys = [];
            foreach ($tableJoins as $table) {
                foreach ($joinsdef as $key => $join) {
                    if ($join['table'] === $table) {
                        $joinKeys[] = $key;
                        break;
                    }
                }
            }
            $state['joins'] = $joinKeys;
        }
    }

    if (!empty($state['base']) && !empty($state['fields'])) {
        return $state;
    }
    return null;
}

}
