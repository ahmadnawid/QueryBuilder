<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_externalpage(
        'report_querybuilder',
        get_string('pluginname', 'report_querybuilder'),
        new moodle_url('/report/querybuilder/index.php')
    ));
}

