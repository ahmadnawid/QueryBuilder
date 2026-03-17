<?php
namespace report_querybuilder\domain;

defined('MOODLE_INTERNAL') || die();

class entities {

    public static function list() {
        return [

            'user' => [
                'name' => 'User',
                'table' => 'user',
                'fields' => [
                    'id',
                    'firstname',
                    'lastname',
                    'email',
                    'lastaccess'
                ],
                'joins' => [
                    'enrolments' => [
                        'entity' => 'enrolment',
                        'table'  => 'user_enrolments',
                        'alias'  => 'ue',
                        'on'     => 'ue.userid = t.id'
                    ],
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = ue.enrolid'
                    ]
                ]
            ],

            'enrolment' => [
                'name' => 'Enrolment',
                'table' => 'user_enrolments',
                'fields' => [
                    'id',
                    'userid',
                    'enrolid',
                    'timestart',
                    'timeend'
                ],
                'joins' => []
            ],

            'course' => [
                'name' => 'Course',
                'table' => 'course',
                'fields' => [
                    'id',
                    'fullname',
                    'shortname',
                    'category'
                ],
                'joins' => []
            ]
        ];
    }
}

