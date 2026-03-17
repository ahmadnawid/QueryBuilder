<?php
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
