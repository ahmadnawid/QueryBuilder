<?php
namespace report_querybuilder\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class sql_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;
	$mform->addElement('html', '<div class="sqlrunner-toolbar" style="margin-bottom:15px;">');
	//$mform->addElement('submit', 'runquery', get_string('runquery', 'report_sqlrunner'));
	//$mform->addElement('submit', 'clearquery', get_string('clearquery', 'report_sqlrunner'));

	$mform->addElement('html', '</div>');


        $mform->addElement('textarea', 'query', get_string('sqlquery', 'report_sqlrunner'),
	'rows="10" cols="120"');
        $mform->setType('query', PARAM_RAW);
        $mform->addRule('query', null, 'required', null, 'client');

	$mform->addElement('text', 'savename', get_string('savename', 'report_sqlrunner'));
	$mform->setType('savename', PARAM_TEXT);

	$mform->addElement('text', 'category', get_string('category', 'report_querybuilder'));
	$mform->setType('category', PARAM_TEXT);

	if (empty($this->_customdata['loadingmode'])) {
		$mform->addElement('advcheckbox', 'savequery', get_string('savequery', 'report_sqlrunner'));
	}
	$mform->addElement('hidden', 'savedqueryid', 0);
	$mform->setType('savedqueryid', PARAM_INT);

        $this->add_action_buttons(false, get_string('runquery', 'report_sqlrunner'));
    }
}

