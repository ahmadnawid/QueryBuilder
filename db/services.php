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
 * External service definitions for Query Builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
