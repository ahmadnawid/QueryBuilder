<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Visual query builder form definition.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_querybuilder\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use report_querybuilder\domain\entities;

/**
 * Form class for the visual query builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class builder_form extends \moodleform {

    /**
     * Defines the form elements for the visual query builder.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $entities = entities::list();

        // Values passed from index.php via customdata.
        $base         = $this->_customdata['base'] ?? null;
        $joinsselected = $this->_customdata['joins'] ?? [];
        $filterfield   = $this->_customdata['filter_field'] ?? '';
        $filterop      = $this->_customdata['filter_op'] ?? '';
        $filtervalue   = $this->_customdata['filter_value'] ?? '';

        // Base entity selector.
        $entityoptions = [];
        foreach ($entities as $key => $entity) {
            $entityoptions[$key] = $entity['name'];
        }

        $mform->addElement('select', 'base', get_string('baseentity', 'report_querybuilder'), $entityoptions, [
            'onchange' => 'this.form.submit();',
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
        if (!empty($joinsselected)) {
            $mform->setDefault('joins', $joinsselected);
        }

        // Filter field.
        $mform->addElement('text', 'filter_field', get_string('filterfield', 'report_querybuilder'));
        $mform->setType('filter_field', PARAM_TEXT);
        $mform->setDefault('filter_field', $filterfield);

        // Filter operator.
        $mform->addElement('select', 'filter_op', get_string('filterop', 'report_querybuilder'), [
            '='    => '=',
            '>'    => '>',
            '<'    => '<',
            'LIKE' => 'LIKE',
        ]);
        if ($filterop) {
            $mform->setDefault('filter_op', $filterop);
        }

        // Filter value.
        $mform->addElement('text', 'filter_value', get_string('filtervalue', 'report_querybuilder'));
        $mform->setType('filter_value', PARAM_TEXT);
        $mform->setDefault('filter_value', $filtervalue);

        $this->add_action_buttons(false, get_string('generatesql', 'report_querybuilder'));
    }
}
