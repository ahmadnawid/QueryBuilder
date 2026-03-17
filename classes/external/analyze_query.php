<?php
namespace report_querybuilder\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/report/querybuilder/classes/local/sql_validator.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use report_querybuilder\local\sql_validator;

class analyze_query extends \external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'sql' => new external_value(PARAM_RAW, 'SQL query to analyze'),
        ]);
    }

    public static function execute(string $sql): array {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), ['sql' => $sql]);
        $sql    = $params['sql'];

        // Check capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('report/querybuilder:view', $context);

        // Validate SQL is safe
        $error = sql_validator::validate($sql);
        if ($error) {
            throw new \invalid_parameter_exception($error);
        }

        try {
            $explainsql = 'EXPLAIN (FORMAT JSON, ANALYZE false, VERBOSE false) ' . $sql;
            $rows       = $DB->get_records_sql($explainsql);
            $raw        = reset($rows);
            $raw        = (array)$raw;
            $jsonstr    = isset($raw['query plan']) ? $raw['query plan'] : reset($raw);
            $plandata   = json_decode($jsonstr, true);

            if (!$plandata || !isset($plandata[0]['Plan'])) {
                throw new \moodle_exception('Could not parse EXPLAIN output.');
            }

            $steps    = [];
            $warnings = [];
            self::flatten_plan($plandata[0]['Plan'], $steps, $warnings, 0);

            return [
                'steps'    => $steps,
                'warnings' => $warnings,
            ];

        } catch (\Exception $e) {
            throw new \moodle_exception('generalexceptionmessage', 'error', '', $e->getMessage());
        }
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'steps' => new external_multiple_structure(
                new external_single_structure([
                    'depth'      => new external_value(PARAM_INT,   'Nesting depth'),
                    'node_type'  => new external_value(PARAM_TEXT,  'Node type'),
                    'relation'   => new external_value(PARAM_TEXT,  'Table or relation', VALUE_OPTIONAL, ''),
                    'index'      => new external_value(PARAM_TEXT,  'Index used',        VALUE_OPTIONAL, ''),
                    'rows_est'   => new external_value(PARAM_FLOAT, 'Estimated rows',    VALUE_OPTIONAL, 0),
                    'width'      => new external_value(PARAM_INT,   'Plan width',        VALUE_OPTIONAL, 0),
                    'cost_total' => new external_value(PARAM_FLOAT, 'Total cost',        VALUE_OPTIONAL, 0),
                    'filter'     => new external_value(PARAM_TEXT,  'Filter condition',  VALUE_OPTIONAL, ''),
                    'warnings'   => new external_multiple_structure(
                        new external_single_structure([
                            'level'   => new external_value(PARAM_TEXT, 'Warning level'),
                            'message' => new external_value(PARAM_TEXT, 'Warning message'),
                        ])
                    ),
                ])
            ),
            'warnings' => new external_multiple_structure(
                new external_single_structure([
                    'level'   => new external_value(PARAM_TEXT, 'Warning level'),
                    'message' => new external_value(PARAM_TEXT, 'Warning message'),
                ])
            ),
        ]);
    }

    private static function flatten_plan(array $node, array &$steps, array &$warnings, int $depth): void {
        $nodetype   = $node['Node Type']     ?? '';
        $relation   = $node['Relation Name'] ?? ($node['Alias'] ?? '');
        $rows_est   = $node['Plan Rows']     ?? 0;
        $width      = $node['Plan Width']    ?? 0;
        $cost_total = $node['Total Cost']    ?? 0;
        $index_name = $node['Index Name']    ?? null;
        $index_cond = $node['Index Cond']    ?? null;
        $filter     = $node['Filter']        ?? '';
        $join_type  = $node['Join Type']     ?? null;
        $strategy   = $node['Strategy']      ?? null;

        $stepwarnings = [];

        if (strtolower($nodetype) === 'seq scan' && !empty($relation)) {
            $stepwarnings[] = [
                'level'   => 'danger',
                'message' => 'Sequential scan on "' . $relation . '" — no index used. Consider adding an index.',
            ];
        }

        if ((int)$rows_est > 100000) {
            $stepwarnings[] = [
                'level'   => 'warning',
                'message' => 'Step estimates ' . number_format($rows_est) . ' rows'
                    . (!empty($relation) ? ' on "' . $relation . '"' : '') . '.',
            ];
        }

        if (in_array(strtolower($nodetype), ['hash join', 'nested loop']) && (int)$rows_est > 10000) {
            $stepwarnings[] = [
                'level'   => 'warning',
                'message' => strtoupper($nodetype) . ' over ' . number_format($rows_est) . ' rows. Check join indexes.',
            ];
        }

        $indexinfo = $index_name
            ? $index_name . ($index_cond ? ' ON ' . $index_cond : '')
            : '';

        $steps[] = [
            'depth'      => $depth,
            'node_type'  => $nodetype
                . ($join_type ? ' (' . $join_type . ')' : '')
                . ($strategy  ? ' [' . $strategy  . ']' : ''),
            'relation'   => (string)$relation,
            'index'      => (string)$indexinfo,
            'rows_est'   => (float)$rows_est,
            'width'      => (int)$width,
            'cost_total' => (float)$cost_total,
            'filter'     => (string)$filter,
            'warnings'   => $stepwarnings,
        ];

        $warnings = array_merge($warnings, $stepwarnings);

        if (!empty($node['Plans'])) {
            foreach ($node['Plans'] as $child) {
                self::flatten_plan($child, $steps, $warnings, $depth + 1);
            }
    }
}
}
