<?php
namespace report_querybuilder\local;

defined('MOODLE_INTERNAL') || die();

class query_manager {

    public static function get_all() {
        global $DB;
        return $DB->get_records('report_querybuilder_queries', null, 'name ASC');
    }

    public static function get($id) {
        global $DB;
        return $DB->get_record('report_querybuilder_queries', ['id' => $id]);
    }

    public static function delete($id) {
        global $DB;
        return $DB->delete_records('report_querybuilder_queries', ['id' => $id]);
    }

    public static function update($id, $name, $sql, $category = null) {
        global $DB;
        $record = $DB->get_record('report_querybuilder_queries', ['id' => $id]);
        if (!$record) {
            return false;
        }
        $record->name         = $name;
        $record->querytext    = $sql;
	$record->category     = $category;
        $record->timemodified = time();
        return $DB->update_record('report_querybuilder_queries', $record);
    }

    public static function insert($name, $sql, $category = null) {
        global $DB;
        $record = (object)[
            'name'         => $name,
            'querytext'    => $sql,
	    'category'	   => $category,
            'timecreated'  => time(),
            'timemodified' => time()
        ];
        return $DB->insert_record('report_querybuilder_queries', $record);
    }
}

