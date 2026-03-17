<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'report_querybuilder_analyze_query' => [
        'classname'     => 'report_querybuilder\external\analyze_query',
        'methodname'    => 'execute',
        'description'   => 'Runs EXPLAIN on a SQL query and returns the plan.',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'report/querybuilder:view',
    ],
];
