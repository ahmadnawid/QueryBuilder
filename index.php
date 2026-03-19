<?php

require('../../config.php');

require_once($CFG->dirroot . '/report/querybuilder/classes/local/query_manager.php');
require_once($CFG->dirroot . '/report/querybuilder/classes/local/sql_utils.php');
require_once($CFG->dirroot . '/report/querybuilder/classes/local/sql_validator.php');

use report_querybuilder\local\query_manager;
use report_querybuilder\local\sql_utils;
use report_querybuilder\local\sql_validator;
use report_querybuilder\form\builder_form;
use report_querybuilder\query\compiler;


require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_capability('report/querybuilder:view', $context);

$PAGE->set_url('/report/querybuilder/index.php');
$PAGE->set_title(get_string('pagetitle', 'report_querybuilder'));
$PAGE->set_heading(get_string('pageheading', 'report_querybuilder'));

$PAGE->requires->jquery();  // Moodle's built-in jQuery


$sqlparam = optional_param('sql', '', PARAM_RAW);
$advsql = optional_param('advsql', '', PARAM_RAW);
$advanced = optional_param('advanced', 0, PARAM_BOOL);

if (!empty($advsql) && !$advanced) {
    require_sesskey();
    $error = sql_validator::validate($advsql);

    $download = optional_param('download', '', PARAM_ALPHA);

    if(!$download){
    echo $OUTPUT->header();
    echo html_writer::tag('h3', get_string('sql_results_heading', 'report_querybuilder'));
    }

    if ($error) {
	echo $OUTPUT->notification(s($error), 'error');
    } else {
        try {
            global $DB;


$recordset = $DB->get_recordset_sql($advsql);
$columns = [];


if ($recordset->valid()) {
    $first = $recordset->current();
    $columns = array_keys((array)$first);
}
$recordset->close();

$renderer = $PAGE->get_renderer('report_querybuilder');
if ($columns) {
    echo $renderer->render_results_table($columns, $advsql, $download);
} else {
    echo $OUTPUT->notification('No results found.', 'info');
}

         echo $renderer->back_button();

        } catch (Exception $e) {
		echo $OUTPUT->notification(s($e->getMessage()), 'error');
        }
    }
    if(!$download){
    echo $OUTPUT->footer();
    exit;
    }
}

if ($advanced) {
    echo $OUTPUT->header();

    $saved = query_manager::get_all();
    $loadid  = optional_param('loadquery', 0, PARAM_INT);
    $newquery = optional_param('newbutton', null, PARAM_RAW);
    $prefill = null;
    $shownewform = false;
    if ($newquery !== null) {
        $shownewform = true;
    }
    if ($shownewform) {
        $sqlcontent = '';
    }

    // Only set $prefill when Edit is clicked
    if (optional_param('editbutton', null, PARAM_RAW) && $loadid) {
        $prefill = query_manager::get($loadid);
    }

    // DELETE
    if (optional_param('deletebutton', null, PARAM_RAW)) {
        require_sesskey();
        $deleteid = $loadid ?: optional_param('savedqueryid', 0, PARAM_INT);
        if ($deleteid) {
            query_manager::delete($deleteid);
	    redirect(
    new moodle_url('/report/querybuilder/index.php', ['advanced' => 1]),
    get_string('query_deleted', 'report_querybuilder'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
        }
    }

    // SAVE
    if (optional_param('savequery', null, PARAM_RAW)) {
	require_sesskey();
        $savename = required_param('savename', PARAM_TEXT);
        $query    = required_param('query', PARAM_RAW);
        $savedqueryid = optional_param('savedqueryid', 0, PARAM_INT);
	$category = optional_param('category', '', PARAM_TEXT);

	if ($savename && $query) {
        if ($savedqueryid) {
            query_manager::update($savedqueryid, $savename, $query, $category);
            redirect(
                new moodle_url('/report/querybuilder/index.php', [
                    'advanced' => 1,
                    'categoryfilter' => $category // keep user in the same category
                ]),
                get_string('query_updated', 'report_querybuilder'),
                null,
		\core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            query_manager::insert($savename, $query, $category);
            redirect(
                new moodle_url('/report/querybuilder/index.php', [
                    'advanced' => 1,
                    'categoryfilter' => $category // keep user in the same category
                ]),
                get_string('query_saved', 'report_querybuilder'),
                null,
		\core\output\notification::NOTIFY_SUCCESS
            );
        }

 } else {
        echo $OUTPUT->notification('Please enter a name and query.', 'error');
    }

}




$categories = [];
if ($saved) {
    foreach ($saved as $s) {
        if (!empty($s->category)) {
            $categories[$s->category] = $s->category;
        }
    }
}

$selectedcategory = optional_param('categoryfilter', '', PARAM_TEXT);

// Filter saved queries by category
$filtered = [];
if ($saved) {
    foreach ($saved as $s) {
        if ($selectedcategory === '' || $s->category === $selectedcategory) {
            $filtered[] = $s;
        }
    }
}

// Build options for saved query dropdown
$options = ['' => get_string('select_saved_query', 'report_querybuilder')];
foreach ($filtered as $s) {
    $options[$s->id] = $s->name . (!empty($s->category) ? ' (' . $s->category . ')' : '');
}

// Build JS object keyed by ID
$filtered_by_id = [];
foreach ($filtered as $s) {
    $filtered_by_id[$s->id] = $s;
}

// ── Toolbar ───────────────────────────────────────────────────
$categoryurl = new moodle_url('/report/querybuilder/index.php', [
    'advanced'  => 1,
    'loadquery' => $loadid,
]);
$categoryselectobj = new single_select(
    $categoryurl,
    'categoryfilter',
    ['' => get_string('all_categories', 'report_querybuilder')] + $categories,
    $selectedcategory,
    null
);
$categoryselectobj->set_label(get_string('category', 'report_querybuilder'));
echo html_writer::start_div('d-flex align-items-center gap-3 mb-2 flex-wrap');
echo $OUTPUT->render($categoryselectobj);

// Saved query selector
echo html_writer::start_div('d-flex align-items-center gap-2');
echo html_writer::tag('label',
    get_string('select_saved_query', 'report_querybuilder'),
    ['class' => 'me-1']
);
echo html_writer::select(
    $options,
    'loadquery',
    $loadid,
    null,
    ['id' => 'loadqueryselect', 'class' => 'form-select d-inline-block w-auto me-2']
);
echo html_writer::end_div(); // saved query row
echo html_writer::end_div(); // toolbar top row

echo html_writer::start_div('d-flex gap-2 mb-3');

$newurl = new moodle_url('/report/querybuilder/index.php', [
    'advanced'  => 1,
    'newbutton' => 1,
    'sesskey'   => sesskey(),
]);
echo $OUTPUT->single_button($newurl, get_string('btn_new', 'report_querybuilder'), 'get');

// Edit form — action URL updated by JS when query selected
echo html_writer::start_tag('form', [
    'method' => 'post',
    'id'     => 'editqueryform',
    'action' => (new moodle_url('/report/querybuilder/index.php'))->out(false),
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey',     'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'advanced',    'value' => 1]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'loadquery',   'id'    => 'editloadquery', 'value' => $loadid]);
echo html_writer::empty_tag('input', [
    'type'     => 'submit',
    'name'     => 'editbutton',
    'value'    => get_string('btn_edit', 'report_querybuilder'),
    'class'    => 'btn btn-outline-secondary',
    'id'       => 'editquerybtn',
    'disabled' => $loadid ? null : 'disabled',
]);
echo html_writer::end_tag('form');

// Delete form
echo html_writer::start_tag('form', [
    'method' => 'post',
    'id'     => 'deletequeryform',
    'action' => (new moodle_url('/report/querybuilder/index.php'))->out(false),
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey',     'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'advanced',    'value' => 1]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'loadquery',   'id'    => 'deleteloadquery', 'value' => $loadid]);
echo html_writer::empty_tag('input', [
    'type'     => 'submit',
    'name'     => 'deletebutton',
    'value'    => get_string('btn_delete', 'report_querybuilder'),
    'class'    => 'btn btn-outline-danger',
    'id'       => 'deletequerybtn',
    'disabled' => $loadid ? null : 'disabled',
    'onclick'  => "return confirm('" . get_string('confirm_delete', 'report_querybuilder') . "');",
]);
echo html_writer::end_tag('form');

echo html_writer::end_div();


// ── End toolbar ───────────────────────────────────────────────

        // Add this JS after the form:

$queriesjs = json_encode($filtered_by_id);
echo <<<QUERYJS
<script>
document.addEventListener('DOMContentLoaded', function() {
    var select    = document.getElementById('loadqueryselect');
    var textarea  = document.getElementById('advsql');
    var editbtn   = document.getElementById('editquerybtn');
    var deletebtn = document.getElementById('deletequerybtn');
    var editid    = document.getElementById('editloadquery');
    var deleteid  = document.getElementById('deleteloadquery');
    var queries   = $queriesjs;

    if (select && textarea) {
        select.addEventListener('change', function() {
            var id = select.value;
            if (id && queries[id]) {
                textarea.value = queries[id].querytext;
            }
            if (editbtn)   { editbtn.disabled   = !id; }
            if (deletebtn) { deletebtn.disabled = !id; }
            if (editid)    { editid.value   = id; }
            if (deleteid)  { deleteid.value = id; }
        });
    }
});
</script>
QUERYJS;

    // --- Decide what SQL to show in the textarea ---
    if ($prefill) {
        $sqlcontent = $prefill->querytext;
    } elseif (!empty($advsql)) {
        $sqlcontent = $advsql;
    } elseif (!empty($sqlparam)) {
        $sqlcontent = $sqlparam;
    } else {
        $sqlcontent = 'SELECT ...';
    }

// --- Show Save Query Form only if editing ---
    if ($prefill || $shownewform) {
        echo $OUTPUT->box_start('generalbox mb-3');
        echo $OUTPUT->heading(
            $prefill
                ? get_string('btn_edit', 'report_querybuilder') . ': ' . s($prefill->name)
                : get_string('btn_new',  'report_querybuilder'),
            5
        );
        echo html_writer::start_tag('form', [
            'method' => 'post',
            'class'  => 'd-flex align-items-center gap-2 flex-wrap',
            'id'     => 'savequeryform',
        ]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey',      'value' => sesskey()]);
	echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'advanced', 'value' => 1]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'savedqueryid', 'value' => !empty($prefill->id) ? $prefill->id : '']);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'query',        'id' => 'hiddenquery', 'value' => htmlspecialchars($sqlcontent)]);
        echo html_writer::empty_tag('input', [
            'type'        => 'text', 'name' => 'category',
            'placeholder' => get_string('category', 'report_querybuilder'),
            'class'       => 'form-control',
            'style'       => 'width:150px;',
            'value'       => !empty($prefill->category) ? $prefill->category : '',
        ]);
        echo html_writer::empty_tag('input', [
            'type'        => 'text', 'name' => 'savename',
            'placeholder' => get_string('query_name_placeholder', 'report_querybuilder'),
            'class'       => 'form-control',
            'style'       => 'width:220px;',
            'value'       => !empty($prefill->name) ? $prefill->name : '',
        ]);

	echo html_writer::empty_tag('input', [
    'type'  => 'submit',
    'name'  => 'savequery',
    'value' => get_string('btn_save_query', 'report_querybuilder'),
    'class' => 'btn btn-primary',
]);
        echo html_writer::end_tag('form');
        echo $OUTPUT->box_end();
    }


    // --- Toggle button ---
    $togglesql = '';
    if (!empty($advsql)) {
        $togglesql = $advsql;
    } elseif (!empty($sqlcontent) && $sqlcontent !== 'SELECT ...') {
        $togglesql = $sqlcontent;
    }

echo html_writer::start_div('d-flex justify-content-between align-items-center mb-2');
echo $OUTPUT->heading(get_string('advanced_editor_heading', 'report_querybuilder'), 3, 'mb-0');
echo html_writer::link(
    new moodle_url('/report/querybuilder/index.php', ['sql' => $togglesql]),
    get_string('btn_switch_builder', 'report_querybuilder'),
    ['class' => 'btn btn-outline-secondary btn-sm', 'id' => 'modeswitchbtn']
);
echo html_writer::end_div();



echo $OUTPUT->box_start('generalbox');

// SQL editor form
echo html_writer::start_tag('form', [
    'method' => 'post',
    'class'  => 'mb-3',
    'id'     => 'sqleditorform',
    'action' => (new moodle_url('/report/querybuilder/index.php'))->out(false),
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::tag('label',
    get_string('advanced_editor_heading', 'report_querybuilder'),
    ['for' => 'advsql', 'class' => 'form-label fw-semibold mb-1']
);
echo html_writer::tag('textarea', htmlspecialchars($sqlcontent), [
    'id'           => 'advsql',
    'name'         => 'advsql',
    'class'        => 'form-control font-monospace',
    'style'        => 'height:320px; resize:vertical;',
    'autocomplete' => 'off',
    'spellcheck'   => 'false',
]);

// Button row
echo html_writer::start_div('mt-3 d-flex gap-2 align-items-center');
echo html_writer::tag('button',
    get_string('btn_run_sql', 'report_querybuilder'),
    ['type' => 'submit', 'class' => 'btn btn-primary']
);
echo html_writer::tag('button',
    get_string('analyze_query', 'report_querybuilder'),
    ['type' => 'button', 'class' => 'btn btn-outline-secondary', 'id' => 'analyzebtn']
);
echo html_writer::tag('span', '', [
    'id'    => 'analyzespinner',
    'style' => 'display:none;',
    'class' => 'spinner-border spinner-border-sm ms-1',
]);
echo html_writer::end_div();
echo html_writer::tag('small',
    get_string('sql_editor_help', 'report_querybuilder'),
    ['class' => 'text-muted mt-1 d-block']
);

echo html_writer::end_tag('form');

// --- EXPLAIN PLAN PANEL (shown below editor after analyze) ---
echo html_writer::start_div('mt-3', ['id' => 'explainpanel', 'style' => 'display:none;']);
echo html_writer::start_div('card');

// Card header
echo html_writer::start_div('card-header d-flex justify-content-between align-items-center');
echo html_writer::tag('strong', get_string('explain_plan', 'report_querybuilder'));
echo html_writer::tag('button', 'Close', [
    'type'    => 'button',
    'class'   => 'btn btn-sm btn-outline-secondary',
    'id'      => 'explainclosebtn',
]);
echo html_writer::end_div();

// Card body: warnings then plan table
echo html_writer::start_div('card-body');
echo html_writer::tag('div', '', ['id' => 'explainwarnings']);
echo html_writer::tag('div', '', ['id' => 'explaintable', 'class' => 'mt-3']);
echo html_writer::end_div(); // card-body

echo html_writer::end_div(); // card
echo html_writer::end_div(); // explainpanel
// --- END EXPLAIN PANEL ---

echo $OUTPUT->box_end();



// Replace the entire heredoc block with just this:
$PAGE->requires->js_call_amd('report_querybuilder/analyze', 'init');


    // --- JS to keep save form's hidden query field in sync with textarea ---
$PAGE->requires->js_init_code("
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('modeswitchbtn');
        var textarea = document.getElementById('advsql');
        var hiddenquery = document.getElementById('hiddenquery');
        var saveform = document.getElementById('savequeryform');
        if (toggle && textarea) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var sql = textarea.value;
                var url = new URL(toggle.href, window.location.origin);
                url.searchParams.set('sql', sql);
                window.location.href = url.toString();
            });
        }
        if (saveform && textarea && hiddenquery) {
            saveform.addEventListener('submit', function() {
                hiddenquery.value = textarea.value;
            });
        }
    });
    ");

    echo $OUTPUT->footer();
    exit;
}



if (!$advanced) {
    echo $OUTPUT->header();

    $base = optional_param('base', null, PARAM_TEXT);
    $joins = isset($_REQUEST['joins']) ? $_REQUEST['joins'] : [];
    $filter_field = optional_param('filter_field', '', PARAM_TEXT);
    $filter_op = optional_param('filter_op', '', PARAM_TEXT);
    $filter_value = optional_param('filter_value', '', PARAM_TEXT);

        // Parse SQL param (if coming from Advanced mode)
    $builderstate = null;
    if (!empty($sqlparam)) {
	echo '<pre>SQL param: ' . htmlspecialchars($sqlparam) . '</pre>';
        $builderstate = sql_utils::parse_sql_to_builder_state($sqlparam);
    }

    // Optional: Show a warning if SQL is too complex
    if (!empty($sqlparam) && !$builderstate) {
        echo html_writer::div('This SQL is too complex to edit in the builder. Please simplify it or reset.', 'alert alert-warning');
    }

    if (!empty($sqlparam)) {
    $builderstate = sql_utils::parse_sql_to_builder_state($sqlparam);
    echo '<pre>Builderstate: ' . print_r($builderstate, true) . '</pre>';
    }

    $form = new builder_form(null, [
        'base' => $builderstate['base'] ?? $base,
	'fields' => $builderstate['fields'] ?? [],
        'joins' => $builderstate['joins'] ?? $joins,
        'filter_field' => $builderstate['filter_field'] ?? $filter_field,
        'filter_op' => $builderstate['filter_op'] ?? $filter_op,
        'filter_value' => $builderstate['filter_value'] ?? $filter_value
    ]);

    // Always get current form values
    $formdata = $form->get_data();
    if (!$formdata) {
        $formvalues = $form->get_submitted_data();
        if ($formvalues) {
            $formdata = $formvalues;
        } else {
            $formdata = (object)[
                'base' => $builderstate['base'] ?? $base,
                'fields' => $builderstate['fields'] ?? [],
                'joins' => $builderstate['joins'] ?? $joins,
                'filter_field' => $builderstate['filter_field'] ?? $filter_field,
                'filter_op' => $builderstate['filter_op'] ?? $filter_op,
                'filter_value' => $builderstate['filter_value'] ?? $filter_value
            ];
        }
    }

    // Always generate SQL if possible
    if (!empty($formdata->base) && !empty($formdata->fields)) {
        $ast = compiler::build_ast(
            $formdata->base,
            $formdata->fields,
            $formdata->joins,
            $formdata->filter_field,
            $formdata->filter_op,
            $formdata->filter_value
        );
        $sql = compiler::compile($ast);
    } else {
        $sql = '';
    }

    // Toggle button
    $generatedsql = $sql ?? '';
echo html_writer::start_div('mb-2');
echo html_writer::link(
    new moodle_url('/report/querybuilder/index.php', ['advanced' => 1, 'sql' => $generatedsql]),
    get_string('btn_switch_advanced', 'report_querybuilder'),
    ['class' => 'btn btn-outline-secondary btn-sm']
);
echo html_writer::end_div();


    // Show SQL preview only if form was submitted
    if ($formdata && !empty($sql)) {
	echo html_writer::tag('h3', get_string('sql_preview_heading', 'report_querybuilder'));
        $highlighted = '<pre><code class="language-sql">' . htmlspecialchars($sql) . '</code></pre>';
        echo $OUTPUT->box_start('generalbox');
        echo format_text($highlighted, FORMAT_HTML);
        echo $OUTPUT->box_end();
    }

    $form->display();
}




