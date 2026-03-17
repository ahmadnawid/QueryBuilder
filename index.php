<?php

require('../../config.php');

require_once($CFG->dirroot . '/report/querybuilder/classes/local/query_manager.php');
require_once($CFG->dirroot . '/report/querybuilder/classes/local/sql_utils.php');
require_once($CFG->dirroot . '/report/querybuilder/classes/local/sql_validator.php');
//require_once($CFG->dirroot . '/report/querybuilder/classes/output/results_table.php');

use report_querybuilder\local\query_manager;
use report_querybuilder\local\sql_utils;
use report_querybuilder\local\sql_validator;
use report_querybuilder\output\results_table;
use report_querybuilder\form\builder_form;
use report_querybuilder\query\compiler;


require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_capability('report/querybuilder:view', $context);

$PAGE->set_url('/report/querybuilder/index.php');
$PAGE->set_title('Query Builder');
$PAGE->set_heading('Query Builder');

$PAGE->requires->jquery();  // Moodle's built-in jQuery
//$PAGE->requires->js(new moodle_url('/report/querybuilder/assets/prism.js'));
//$PAGE->requires->css(new moodle_url('/report/querybuilder/assets/prism.css'));
//$PAGE->requires->css(new moodle_url('/report/querybuilder/styles/datatables.css'));
//$PAGE->requires->js(new moodle_url('/report/querybuilder/amd/src/datatables.js'), false);


$sqlparam = optional_param('sql', '', PARAM_RAW);
$advsql = optional_param('advsql', '', PARAM_RAW);

if (!empty($advsql)) {
    require_login();
    $error = sql_validator::validate($advsql);

    $download = optional_param('download', '', PARAM_ALPHA);

    if(!$download){
    echo $OUTPUT->header();
    echo html_writer::tag('h3', 'SQL Results');
    }

    if ($error) {
        echo html_writer::tag('pre', s($error));
    } else {
        try {
            global $DB;

            // --- Pagination setup ---
            //$page     = optional_param('page', 0, PARAM_INT);
            //$perpage  = 50;
            //$offset   = $page * $perpage;

            // Count total rows using subquery
            //$countsql = "SELECT COUNT(*) FROM (" . $advsql . ") AS sub";
            //$total = $DB->get_field_sql($countsql);

            // Add pagination safely
            //$paginatedsql = $advsql; //. " LIMIT $perpage OFFSET $offset";

            // Execute final SQL
            //$results = $DB->get_records_sql($paginatedsql);


//echo '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>';
//echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">';
//echo '<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>';

$recordset = $DB->get_recordset_sql($advsql);
$columns = [];
//$rows = [];


if ($recordset->valid()) {
    $first = $recordset->current();
    $columns = array_keys((array)$first);
  //  foreach ($recordset as $row) {
    //    $rows[] = (array)$row;
    //}
}
$recordset->close();

$renderer = $PAGE->get_renderer('report_querybuilder');
if ($columns) {
    echo $renderer->render_results_table($columns, $advsql, $download);
    //echo $renderer->datatables_init_script();
} else {
    echo $OUTPUT->notification('No results found.', 'info');
}

            // --- Pagination bar ---
           // if ($total !== false && $total > $perpage) {
             //   $baseurl = new moodle_url('/report/querybuilder/index.php', [
               //     'advanced' => 1,
                 //   'advsql'   => $advsql,
                //]);
                //echo $OUTPUT->paging_bar($total, $page, $perpage, $baseurl);
            //}


	    // --- Export buttons (CSV / PDF) ---
	 //echo $renderer->export_buttons($advsql);
         echo $renderer->back_button();

        } catch (Exception $e) {
            echo html_writer::tag('pre', s($e->getMessage()));
        }
    }
    if(!$download){
    echo $OUTPUT->footer();
    exit;
    }
}


//echo $OUTPUT->header();
//echo $OUTPUT->heading('Query Builder');

$advanced = optional_param('advanced', 0, PARAM_BOOL);



if ($advanced) {
    echo $OUTPUT->header();
    // --- Saved Queries Logic ---
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
        $deleteid = $loadid ?: optional_param('savedqueryid', 0, PARAM_INT);
        if ($deleteid) {
            query_manager::delete($deleteid);
            echo $OUTPUT->notification('Saved query deleted.', 'success');
            redirect(new moodle_url('/report/querybuilder/index.php', ['advanced' => 1]));
        }
    }

    // SAVE
    if (optional_param('savequery', null, PARAM_RAW)) {
        $savename = required_param('savename', PARAM_TEXT);
        $query    = required_param('query', PARAM_RAW);
        $savedqueryid = optional_param('savedqueryid', 0, PARAM_INT);
	$category = optional_param('category', '', PARAM_TEXT);
	$savequeryid = optional_param('savequeryid', 0, PARAM_INT);

	if ($savename && $query) {
        if ($savedqueryid) {
            query_manager::update($savedqueryid, $savename, $query, $category);
            redirect(
                new moodle_url('/report/querybuilder/index.php', [
                    'advanced' => 1,
                    'categoryfilter' => $category // keep user in the same category
                ]),
                get_string('query_updated', 'report_querybuilder'),
                2
            );
        } else {
            query_manager::insert($savename, $query, $category);
            redirect(
                new moodle_url('/report/querybuilder/index.php', [
                    'advanced' => 1,
                    'categoryfilter' => $category // keep user in the same category
                ]),
                get_string('query_saved', 'report_querybuilder'),
                2
            );
        }

 } else {
        echo $OUTPUT->notification('Please enter a name and query.', 'error');
    }

    echo html_writer::script("
    document.addEventListener('DOMContentLoaded', function() {
        var saveform = document.getElementById('savequeryform');
        if (saveform) {
            saveform.addEventListener('submit', function() {
                var btn = saveform.querySelector('input[name=\"savequery\"]');
                if (btn) { btn.disabled = true; }
            });
        }
    });
");

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

echo html_writer::start_tag('form', [
    'method' => 'post',
    'class'  => 'd-flex align-items-center gap-3 mb-3 flex-wrap',
    'id'     => 'categoryfilterform'
]);
echo html_writer::select(
    ['' => 'All categories'] + $categories,
    'categoryfilter',
    $selectedcategory,
    null,
    ['class' => 'form-select me-2', 'onchange' => 'this.form.submit();']
);
echo html_writer::end_tag('form');

// Filter saved queries
$filtered = [];
if ($saved) {
    foreach ($saved as $s) {
        if ($selectedcategory === '' || $s->category === $selectedcategory) {
            $filtered[] = $s;
        }
    }
}
$options = ['' => 'Select saved query'];
foreach ($filtered as $s) {
    $options[$s->id] = $s->name . (!empty($s->category) ? ' (' . $s->category . ')' : '');
}


// Build JS object keyed by ID
$filtered_by_id = [];
foreach ($filtered as $s) {
    $filtered_by_id[$s->id] = $s;
}


        echo html_writer::start_tag('form', [
            'method' => 'post',
            'class'  => 'd-flex align-items-center gap-3 mb-3 flex-wrap',
            'style'  => 'flex-wrap:wrap;',
            'id'     => 'savedquerytoolbar'
        ]);

        echo html_writer::select($options, 'loadquery', $loadid, null, [
            'class' => 'form-select me-2',
            'style' => 'width:auto; min-width:200px;',
            'id'    => 'loadqueryselect'
        ]);


	echo html_writer::empty_tag('input', [
   	     'type'  => 'submit',
    	     'name'  => 'newbutton',
    	     'value' => 'New',
             'class' => 'btn btn-secondary me-1',
	]);

	echo html_writer::empty_tag('input', [
            'type'  => 'submit',
            'name'  => 'editbutton',
            'value' => 'Edit',
            'class' => 'btn btn-secondary me-1',
        ]);

        echo html_writer::empty_tag('input', [
            'type'    => 'submit',
            'name'    => 'deletebutton',
            'value'   => 'Delete',
            'class'   => 'btn btn-secondary',
            'onclick' => "var sel=document.getElementsByName('loadquery')[0];
                          var name=sel.options[sel.selectedIndex].text;
                          if (!sel.value) { alert('Please select a query to delete.'); return false; }
                          return confirm('Are you sure you want to delete \"' + name + '\"?');",
        ]);

        echo html_writer::end_tag('form');


        // Add this JS after the form:
        echo html_writer::script("
            document.addEventListener('DOMContentLoaded', function() {
                var select = document.getElementById('loadqueryselect');
                var textarea = document.getElementById('advsql');
                var queries = " . json_encode($filtered_by_id) . ";
                if (select && textarea) {
                    select.addEventListener('change', function() {
                        var id = select.value;
                        if (id && queries[id]) {
                            textarea.value = queries[id].querytext;
                        }
                    });
                }
            });
        ");

    // --- Decide what SQL to show in the textarea ---
    if ($prefill) {
        $sqlcontent = $prefill->querytext;
    } elseif (!empty($advsql)) {
        $sqlcontent = $advsql;
    } elseif (!empty($sqlparam)) {
        $sqlcontent = $sqlparam;
    } elseif (isset($sql)) {
        $sqlcontent = $sql;
    } else {
        $sqlcontent = 'SELECT ...';
    }

    // --- Show Save Query Form only if editing ---
    if ($prefill || $shownewform) {
        echo html_writer::start_tag('form', [
            'method' => 'post',
            'class'  => 'd-flex align-items-center gap-2 mb-3',
            'style'  => 'flex-wrap:wrap;',
            'id'     => 'savequeryform'
        ]);

	echo html_writer::empty_tag('input', [
        'type' => 'text',
        'name' => 'category',
        'placeholder' => get_string('category', 'report_querybuilder'),
        'class' => 'form-control me-2',
        'style' => 'width:auto; min-width:120px;',
        'value' => !empty($prefill->category) ? $prefill->category : '',
        ]);

        echo html_writer::empty_tag('input', [
            'type'  => 'text',
            'name'  => 'savename',
            'placeholder' => 'Query name',
            'value' => !empty($prefill->name) ? $prefill->name : '',
            'class' => 'form-control me-2',
            'style' => 'width:auto; min-width:200px;',
        ]);

        echo html_writer::empty_tag('input', [
            'type'  => 'hidden',
            'name'  => 'savedqueryid',
            'value' => !empty($prefill->id) ? $prefill->id : '',
        ]);

        echo html_writer::empty_tag('input', [
            'type'  => 'hidden',
            'name'  => 'query',
            'id'    => 'hiddenquery',
            'value' => htmlspecialchars($sqlcontent),
        ]);

        echo html_writer::empty_tag('input', [
            'type'  => 'submit',
            'name'  => 'savequery',
            'value' => 'Save Query',
            'class' => 'btn btn-secondary',
        ]);

        echo html_writer::end_tag('form');
    }

    // --- Toggle button ---
    $togglesql = '';
    if (!empty($advsql)) {
        $togglesql = $advsql;
    } elseif (!empty($sqlcontent) && $sqlcontent !== 'SELECT ...') {
        $togglesql = $sqlcontent;
    }

    echo html_writer::start_div('mode-switch');
    echo html_writer::link(
        new moodle_url('/report/querybuilder/index.php', ['sql' => $togglesql]),
        'Switch to Builder Mode'
    );
    echo html_writer::end_div();

    echo html_writer::tag('h3', 'Advanced SQL Editor');

    // --- SQL Editor ---
    $editor = '
        <form method="post" class="mb-3" id="sqleditorform">
            <textarea id="advsql" name="advsql" class="language-sql form-control" style="width:100%; height:300px;">'
            . htmlspecialchars($sqlcontent) .
            '</textarea>
            <br>
            <button type="submit" class="btn btn-secondary mt-2">Run SQL</button>
        </form>
    ';

//    echo $OUTPUT->box_start('generalbox');
  //  echo $editor;
    //echo $OUTPUT->box_end();


echo $OUTPUT->box_start('generalbox');

// SQL editor form
echo html_writer::start_tag('form', ['method' => 'post', 'class' => 'mb-3', 'id' => 'sqleditorform']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::tag('textarea', htmlspecialchars($sqlcontent), [

    'id'    => 'advsql',
    'name'  => 'advsql',
    'class' => 'language-sql form-control',
    'style' => 'width:100%; height:300px;',
]);

// Button row
echo html_writer::start_div('mt-2 d-flex gap-2 align-items-center');
echo html_writer::tag('button', get_string('btn_run_sql', 'report_querybuilder'), [
    'type'  => 'submit',
    'class' => 'btn btn-secondary me-1',
]);
echo html_writer::tag('button', get_string('analyze_query', 'report_querybuilder'), [
    'type'    => 'button',
    'class'   => 'btn btn-secondary',
    'id'      => 'analyzebtn',
]);
echo html_writer::tag('span', '', [
    'id'    => 'analyzespinner',
    'style' => 'display:none;',
    'class' => 'spinner-border spinner-border-sm text-info ms-2',
]);
echo html_writer::end_div();

echo html_writer::end_tag('form');

// --- EXPLAIN PLAN PANEL (shown below editor after analyze) ---
echo html_writer::start_div('mt-3', ['id' => 'explainpanel', 'style' => 'display:none;']);
echo html_writer::start_div('card');

// Card header
echo html_writer::start_div('card-header d-flex justify-content-between align-items-center');
echo html_writer::tag('strong', get_string('explain_plan', 'report_querybuilder'));
echo html_writer::tag('button', '✕ Close', [
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
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.querySelector('.mode-switch a');
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
    </script>
    <?php

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
    echo html_writer::start_div('mode-switch');
    echo html_writer::link(
        new moodle_url('/report/querybuilder/index.php', ['advanced' => 1, 'sql' => $generatedsql]),
        'Switch to Advanced SQL Mode'
    );
    echo html_writer::end_div();

    // Show SQL preview only if form was submitted
    if ($formdata && !empty($sql)) {
        echo html_writer::tag('h3', 'SQL Preview');
        $highlighted = '<pre><code class="language-sql">' . htmlspecialchars($sql) . '</code></pre>';
        echo $OUTPUT->box_start('generalbox');
        echo format_text($highlighted, FORMAT_HTML);
        echo $OUTPUT->box_end();
    }

    $form->display();
}




