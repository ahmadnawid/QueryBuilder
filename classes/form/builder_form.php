<?php
namespace report_querybuilder\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use report_querybuilder\domain\entities;

class builder_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $entities = entities::list();

        // Values passed from index.php via customdata.
        $base          = $this->_customdata['base'] ?? null;
        $joinsSelected = $this->_customdata['joins'] ?? [];
        $filterField   = $this->_customdata['filter_field'] ?? '';
        $filterOp      = $this->_customdata['filter_op'] ?? '';
        $filterValue   = $this->_customdata['filter_value'] ?? '';

        // Base entity selector.
        $entityoptions = [];
        foreach ($entities as $key => $entity) {
            $entityoptions[$key] = $entity['name'];
        }

        $mform->addElement('select', 'base', get_string('baseentity', 'report_querybuilder'), $entityoptions, [
            'onchange' => 'this.form.submit();'
        ]);
        $mform->setType('base', PARAM_TEXT);
        if ($base) {
            $mform->setDefault('base', $base);
        }

        // Fields selector.
        $fieldoptions = [];
        if ($base && isset($entities[$base])) {
            foreach ($entities[$base]['fields'] as $field) {
                $fieldoptions[$field] = $field;
            }
        }

        $mform->addElement('select', 'fields', get_string('fields', 'report_querybuilder'), $fieldoptions);
        $mform->getElement('fields')->setMultiple(true);

        // Joins selector.
        $joinoptions = [];
        if ($base && isset($entities[$base]['joins'])) {
            foreach ($entities[$base]['joins'] as $key => $join) {
                $joinoptions[$key] = $join['entity'];
            }
        }

        $mform->addElement('select', 'joins', get_string('joins', 'report_querybuilder'), $joinoptions);
        $mform->getElement('joins')->setMultiple(true);
        if (!empty($joinsSelected)) {
            $mform->setDefault('joins', $joinsSelected);
        }

        // Filter field.
        $mform->addElement('text', 'filter_field', get_string('filterfield', 'report_querybuilder'));
        $mform->setType('filter_field', PARAM_TEXT);
        $mform->setDefault('filter_field', $filterField);

        // Filter operator.
        $mform->addElement('select', 'filter_op', get_string('filterop', 'report_querybuilder'), [
            '='    => '=',
            '>'    => '>',
            '<'    => '<',
            'LIKE' => 'LIKE'
        ]);
        if ($filterOp) {
            $mform->setDefault('filter_op', $filterOp);
        }

        // Filter value.
        $mform->addElement('text', 'filter_value', get_string('filtervalue', 'report_querybuilder'));
        $mform->setType('filter_value', PARAM_TEXT);
        $mform->setDefault('filter_value', $filterValue);

        $this->add_action_buttons(false, get_string('generatesql', 'report_querybuilder'));
    }
}

