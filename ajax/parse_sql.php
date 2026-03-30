<?php
require('../../../config.php');
require_login();
require_capability('report/querybuilder:view', context_system::instance());

header('Content-Type: application/json');

$sql = required_param('sql', PARAM_RAW);

require_once($CFG->dirroot . '/report/querybuilder/classes/local/sql_utils.php');

$builderstate = \report_querybuilder\local\sql_utils::parse_sql_to_builder_state($sql);

if ($builderstate) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => get_string('sql_too_complex', 'report_querybuilder')]);
}
exit;
