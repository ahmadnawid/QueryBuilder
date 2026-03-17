<?php
namespace report_querybuilder\query;

defined('MOODLE_INTERNAL') || die();

use report_querybuilder\domain\entities;

class compiler {

    /**
     * Build a simple AST from form data.
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
                'value' => trim((string)$filtervalue)
            ]
        ];
    }

    /**
     * Compile AST to SQL (no execution, just preview).
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

