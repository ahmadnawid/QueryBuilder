<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']               = 'Query Builder';
$string['pluginname_desc']          = 'Build and run custom SQL reports.';

// Page titles
$string['pagetitle']                = 'Query Builder';
$string['pageheading']              = 'Query Builder';
$string['advanced_editor_heading']  = 'Advanced SQL Editor';
$string['sql_preview_heading']      = 'SQL Preview';
$string['sql_results_heading']      = 'SQL Results';

// Buttons & labels
$string['btn_run_sql']              = 'Run SQL';
$string['btn_save_query']           = 'Save Query';
$string['btn_new']                  = 'New';
$string['btn_edit']                 = 'Edit';
$string['btn_delete']               = 'Delete';
$string['btn_export_csv']           = 'Export CSV';
$string['btn_export_pdf']           = 'Export PDF';
$string['btn_back']                 = 'Back to query form';
$string['btn_switch_advanced']      = 'Switch to Advanced SQL Mode';
$string['btn_switch_builder']       = 'Switch to Builder Mode';
$string['select_saved_query']       = 'Select saved query';
$string['query_name_placeholder']   = 'Query name';
$string['baseentity']  = 'Base entity';
$string['fields']      = 'Fields';
$string['joins']       = 'Joins';
$string['filterfield'] = 'Filter field';
$string['filterop']    = 'Operator';
$string['filtervalue'] = 'Value';
$string['generatesql'] = 'Generate SQL';
$string['category'] = 'Category';
// Notifications
$string['query_saved']              = 'Query saved successfully';
$string['query_updated']            = 'Query updated successfully';
$string['query_deleted']            = 'Saved query deleted.';
$string['error_name_and_query']     = 'Please enter a name and query.';
$string['error_select_to_delete']   = 'Please select a query to delete.';
$string['confirm_delete']           = 'Are you sure you want to delete this query?';
$string['no_results']               = 'No results found.';
$string['sql_too_complex']          = 'This SQL is too complex to edit in the builder. Please simplify it or reset.';

// Validation errors
$string['error_selectonly']         = 'Only SELECT statements are allowed.';
$string['error_multistatement']     = 'Multiple statements are not allowed.';
$string['error_dangerous_keyword']  = 'Dangerous SQL keyword detected: ';

// Capability
$string['querybuilder:view']        = 'View Query Builder';
$string['manage_entities'] = 'Manage Entities';


$string['analyze_query']       = 'Analyze Query';
$string['explain_plan']        = 'Query Explain Plan';
$string['explain_node']        = 'Operation';
$string['explain_relation']    = 'Table / Relation';
$string['explain_index']       = 'Index Used';
$string['explain_rows']        = 'Est. Rows';
$string['explain_cost']        = 'Total Cost';
$string['explain_filter']      = 'Filter';
$string['explain_warnings']    = 'Performance Warnings';
$string['explain_no_warnings'] = 'No performance issues detected.';
$string['explain_analyzing']   = 'Analyzing...';
$string['explain_error']       = 'Could not analyze query: ';
$string['enter_sql'] = 'Please enter a SQL query to analyze.';
$string['all_categories'] = 'All categories';
