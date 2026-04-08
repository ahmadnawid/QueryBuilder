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
 * SQL compiler for the visual query builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_querybuilder\query;

use report_querybuilder\domain\entities;

/**
 * Class for compiling AST to SQL for the visual query builder.
 *
 * @package    report_querybuilder
 * @copyright  2026 Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compiler {

    /**
     * Build a simple AST from form data.
     *
     * @param string $base The base entity key.
     * @param array $fields The selected fields.
     * @param array $joins The selected joins.
     * @param string $filterfield The filter field.
     * @param string $filterop The filter operator.
     * @param string $filtervalue The filter value.
     * @return array The AST array.
     */
    public static function build_ast($base, $fields, $joins, $filterfield, $filterop, $filtervalue): array {
        if (!is_array($fields)) {
            $fields = [];
        }
        if (!is_array($joins)) {
            $joins = [];
        }

        return [
            'base'   => $base,
            'fields' => $fields,
            'joins'  => $joins,
            'filter' => [
                'field' => trim((string)$filterfield),
                'op'    => trim((string)$filterop),
                'value' => trim((string)$filtervalue),
            ],
        ];
    }

    /**
     * Compile AST to SQL (no execution, just preview).
     *
     * @param array $ast The AST array.
     * @return string The compiled SQL string.
     */
    public static function compile(array $ast): string {
        $entities = entities::list();

        $base   = $ast['base'] ?? null;
        $fields = $ast['fields'] ?? [];
        $joins  = $ast['joins'] ?? [];
        $filter = $ast['filter'] ?? [];

        if (!$base || !isset($entities[$base])) {
            return '-- Invalid base entity';
        }

        $baseentity = $entities[$base];
        $basetable  = $baseentity['table'];

        // SELECT clause.
        if (empty($fields)) {
            $selectsql = 't.id';
        } else {
            $select = [];
            foreach ($fields as $field) {
                $select[] = "t.$field";
            }
            $selectsql = implode(', ', $select);
        }

        // FROM clause.
        $sql = "SELECT $selectsql\nFROM {{$basetable}} t";

        // JOIN clauses.
        if (!empty($joins) && !empty($baseentity['joins'])) {
            foreach ($joins as $joinkey) {
                if (!isset($baseentity['joins'][$joinkey])) {
                    continue;
                }
                $join = $baseentity['joins'][$joinkey];
                $jointable = $join['table'];
                $alias     = $join['alias'];
                $on        = $join['on'];

                $sql .= "\nJOIN {{$jointable}} {$alias} ON {$on}";
            }
        }

        // WHERE clause (single simple filter for now).
        $filterfield = $filter['field'] ?? '';
        $filterop    = $filter['op'] ?? '';
        $filtervalue = $filter['value'] ?? '';

        if ($filterfield !== '' && $filterop !== '' && $filtervalue !== '') {
            // For preview we inline the value; for real execution you'd parameterize.
            $safevalue = addslashes($filtervalue);
            $sql .= "\nWHERE {$filterfield} {$filterop} '{$safevalue}'";
        }

        return $sql;
    }
}

