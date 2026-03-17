<?php
namespace report_querybuilder\domain;

defined('MOODLE_INTERNAL') || die();

/**
 * Built-in entity definitions for Moodle 4.5
 *
 * Each entity defines:
 *   name   - human readable label shown in the builder UI
 *   table  - Moodle table name WITHOUT the mdl_ prefix
 *   fields - columns available for SELECT in the builder
 *   joins  - related tables that can be joined from this entity
 *
 * Join definition keys:
 *   entity - the entity key this join connects to (informational)
 *   table  - the table to JOIN (without mdl_ prefix)
 *   alias  - the SQL alias used in the ON clause
 *   on     - the JOIN ON condition
 *            't' always refers to the base entity table alias
 *
 * All timestamps are Unix epoch integers stored in Moodle.
 * Use TO_TIMESTAMP(columnname) in raw SQL to convert for display.
 */
class entities {

    public static function list(): array {
        return [

            // ================================================================
            // USERS
            // ================================================================
            'user' => [
                'name'   => 'User',
                'table'  => 'user',
                'fields' => [
                    'id',
                    'username',
                    'firstname',
                    'lastname',
                    'email',
                    'institution',
                    'department',
                    'city',
                    'country',
                    'lang',
                    'timezone',
                    'lastaccess',
                    'lastlogin',
                    'firstaccess',
                    'timecreated',
                    'timemodified',
                    'suspended',
                    'deleted',
                    'confirmed',
                    'auth',
                    'idnumber',
                    'phone1',
                    'phone2',
                    'description',
                    'currentlogin',
                    'picture',
                    'mnethostid',
                ],
                'joins'  => [
                    'enrolments' => [
                        'entity' => 'enrolment',
                        'table'  => 'user_enrolments',
                        'alias'  => 'ue',
                        'on'     => 'ue.userid = t.id',
                    ],
                    'role_assignments' => [
                        'entity' => 'role_assignment',
                        'table'  => 'role_assignments',
                        'alias'  => 'ra',
                        'on'     => 'ra.userid = t.id',
                    ],
                    'course_completions' => [
                        'entity' => 'course_completion',
                        'table'  => 'course_completions',
                        'alias'  => 'cc',
                        'on'     => 'cc.userid = t.id',
                    ],
                    'grade_grades' => [
                        'entity' => 'grade_grade',
                        'table'  => 'grade_grades',
                        'alias'  => 'gg',
                        'on'     => 'gg.userid = t.id',
                    ],
                    'cohort_members' => [
                        'entity' => 'cohort_member',
                        'table'  => 'cohort_members',
                        'alias'  => 'chm',
                        'on'     => 'chm.userid = t.id',
                    ],
                    'user_lastaccess' => [
                        'entity' => 'user_lastaccess',
                        'table'  => 'user_lastaccess',
                        'alias'  => 'ula',
                        'on'     => 'ula.userid = t.id',
                    ],
                    'log' => [
                        'entity' => 'log',
                        'table'  => 'logstore_standard_log',
                        'alias'  => 'sl',
                        'on'     => 'sl.userid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // COURSES
            // ================================================================
            'course' => [
                'name'   => 'Course',
                'table'  => 'course',
                'fields' => [
                    'id',
                    'fullname',
                    'shortname',
                    'idnumber',
                    'summary',
                    'format',
                    'showgrades',
                    'newsitems',
                    'startdate',
                    'enddate',
                    'visible',
                    'groupmode',
                    'groupmodeforce',
                    'lang',
                    'enablecompletion',
                    'completionnotify',
                    'timecreated',
                    'timemodified',
                    'category',
                    'sortorder',
                    'defaultgroupingid',
                    'cacherev',
                ],
                'joins'  => [
                    'course_category' => [
                        'entity' => 'course_category',
                        'table'  => 'course_categories',
                        'alias'  => 'cat',
                        'on'     => 'cat.id = t.category',
                    ],
                    'enrol' => [
                        'entity' => 'enrol',
                        'table'  => 'enrol',
                        'alias'  => 'en',
                        'on'     => 'en.courseid = t.id',
                    ],
                    'course_completions' => [
                        'entity' => 'course_completion',
                        'table'  => 'course_completions',
                        'alias'  => 'cc',
                        'on'     => 'cc.course = t.id',
                    ],
                    'context' => [
                        'entity' => 'context',
                        'table'  => 'context',
                        'alias'  => 'ctx',
                        'on'     => 'ctx.instanceid = t.id AND ctx.contextlevel = 50',
                    ],
                    'grade_items' => [
                        'entity' => 'grade_item',
                        'table'  => 'grade_items',
                        'alias'  => 'gi',
                        'on'     => 'gi.courseid = t.id AND gi.itemtype = \'course\'',
                    ],
                    'course_modules' => [
                        'entity' => 'course_module',
                        'table'  => 'course_modules',
                        'alias'  => 'cm',
                        'on'     => 'cm.course = t.id',
                    ],
                ],
            ],

            // ================================================================
            // COURSE CATEGORIES
            // ================================================================
            'course_category' => [
                'name'   => 'Course Category',
                'table'  => 'course_categories',
                'fields' => [
                    'id',
                    'name',
                    'idnumber',
                    'description',
                    'parent',
                    'sortorder',
                    'coursecount',
                    'visible',
                    'visibleold',
                    'timemodified',
                    'depth',
                    'path',
                    'theme',
                ],
                'joins'  => [
                    'courses' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.category = t.id',
                    ],
                ],
            ],

            // ================================================================
            // ENROLMENTS
            // ================================================================
            'enrolment' => [
                'name'   => 'Enrolment (User)',
                'table'  => 'user_enrolments',
                'fields' => [
                    'id',
                    'status',
                    'enrolid',
                    'userid',
                    'timestart',
                    'timeend',
                    'modifierid',
                    'timecreated',
                    'timemodified',
                ],
                'joins'  => [
                    'enrol' => [
                        'entity' => 'enrol',
                        'table'  => 'enrol',
                        'alias'  => 'en',
                        'on'     => 'en.id = t.enrolid',
                    ],
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = en.courseid',
                    ],
                ],
            ],

            // ================================================================
            // ENROL METHODS
            // ================================================================
            'enrol' => [
                'name'   => 'Enrolment Method',
                'table'  => 'enrol',
                'fields' => [
                    'id',
                    'enrol',
                    'status',
                    'courseid',
                    'sortorder',
                    'name',
                    'enrolperiod',
                    'enrolstartdate',
                    'enrolenddate',
                    'expirynotify',
                    'expirythreshold',
                    'notifyall',
                    'password',
                    'cost',
                    'currency',
                    'roleid',
                    'customint1',
                    'customint2',
                    'timecreated',
                    'timemodified',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                    'user_enrolments' => [
                        'entity' => 'enrolment',
                        'table'  => 'user_enrolments',
                        'alias'  => 'ue',
                        'on'     => 'ue.enrolid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // ROLES
            // ================================================================
            'role' => [
                'name'   => 'Role',
                'table'  => 'role',
                'fields' => [
                    'id',
                    'name',
                    'shortname',
                    'description',
                    'sortorder',
                    'archetype',
                ],
                'joins'  => [
                    'role_assignments' => [
                        'entity' => 'role_assignment',
                        'table'  => 'role_assignments',
                        'alias'  => 'ra',
                        'on'     => 'ra.roleid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // ROLE ASSIGNMENTS
            // ================================================================
            'role_assignment' => [
                'name'   => 'Role Assignment',
                'table'  => 'role_assignments',
                'fields' => [
                    'id',
                    'roleid',
                    'contextid',
                    'userid',
                    'timemodified',
                    'modifierid',
                    'itemid',
                    'sortorder',
                    'component',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'role' => [
                        'entity' => 'role',
                        'table'  => 'role',
                        'alias'  => 'r',
                        'on'     => 'r.id = t.roleid',
                    ],
                    'context' => [
                        'entity' => 'context',
                        'table'  => 'context',
                        'alias'  => 'ctx',
                        'on'     => 'ctx.id = t.contextid',
                    ],
                ],
            ],

            // ================================================================
            // CONTEXT
            // ================================================================
            'context' => [
                'name'   => 'Context',
                'table'  => 'context',
                'fields' => [
                    'id',
                    'contextlevel',
                    'instanceid',
                    'path',
                    'depth',
                    'locked',
                ],
                'joins'  => [],
            ],

            // ================================================================
            // COURSE COMPLETION
            // ================================================================
            'course_completion' => [
                'name'   => 'Course Completion',
                'table'  => 'course_completions',
                'fields' => [
                    'id',
                    'userid',
                    'course',
                    'timeenrolled',
                    'timestarted',
                    'timecompleted',
                    'reaggregate',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.course',
                    ],
                ],
            ],

            // ================================================================
            // ACTIVITY (COURSE MODULE) COMPLETION
            // ================================================================
            'activity_completion' => [
                'name'   => 'Activity Completion',
                'table'  => 'course_modules_completion',
                'fields' => [
                    'id',
                    'coursemoduleid',
                    'userid',
                    'completionstate',
                    'viewed',
                    'overrideby',
                    'timemodified',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'course_module' => [
                        'entity' => 'course_module',
                        'table'  => 'course_modules',
                        'alias'  => 'cm',
                        'on'     => 'cm.id = t.coursemoduleid',
                    ],
                ],
            ],

            // ================================================================
            // COURSE MODULES
            // ================================================================
            'course_module' => [
                'name'   => 'Course Module (Activity)',
                'table'  => 'course_modules',
                'fields' => [
                    'id',
                    'course',
                    'module',
                    'instance',
                    'section',
                    'idnumber',
                    'added',
                    'score',
                    'indent',
                    'visible',
                    'visibleoncoursepage',
                    'visibleold',
                    'groupmode',
                    'groupingid',
                    'completion',
                    'completiongradeitemnumber',
                    'completionview',
                    'completionexpected',
                    'showdescription',
                    'availability',
                    'deletioninprogress',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.course',
                    ],
                    'modules' => [
                        'entity' => 'module',
                        'table'  => 'modules',
                        'alias'  => 'mod',
                        'on'     => 'mod.id = t.module',
                    ],
                ],
            ],

            // ================================================================
            // GRADE ITEMS
            // ================================================================
            'grade_item' => [
                'name'   => 'Grade Item',
                'table'  => 'grade_items',
                'fields' => [
                    'id',
                    'courseid',
                    'categoryid',
                    'itemname',
                    'itemtype',
                    'itemmodule',
                    'iteminstance',
                    'itemnumber',
                    'iteminfo',
                    'idnumber',
                    'calculation',
                    'gradetype',
                    'grademax',
                    'grademin',
                    'scaleid',
                    'outcomeid',
                    'gradepass',
                    'multfactor',
                    'plusfactor',
                    'aggregationcoef',
                    'aggregationcoef2',
                    'sortorder',
                    'display',
                    'decimals',
                    'hidden',
                    'locked',
                    'locktime',
                    'needsupdate',
                    'weightoverride',
                    'timecreated',
                    'timemodified',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                    'grade_grades' => [
                        'entity' => 'grade_grade',
                        'table'  => 'grade_grades',
                        'alias'  => 'gg',
                        'on'     => 'gg.itemid = t.id',
                    ],
                    'grade_category' => [
                        'entity' => 'grade_category',
                        'table'  => 'grade_categories',
                        'alias'  => 'gc',
                        'on'     => 'gc.id = t.categoryid',
                    ],
                ],
            ],

            // ================================================================
            // GRADE GRADES (actual user grades)
            // ================================================================
            'grade_grade' => [
                'name'   => 'Grade (User Grade)',
                'table'  => 'grade_grades',
                'fields' => [
                    'id',
                    'itemid',
                    'userid',
                    'rawgrade',
                    'rawgrademax',
                    'rawgrademin',
                    'rawscaleid',
                    'usermodified',
                    'finalgrade',
                    'hidden',
                    'locked',
                    'locktime',
                    'exported',
                    'overridden',
                    'excluded',
                    'feedback',
                    'feedbackformat',
                    'information',
                    'informationformat',
                    'timecreated',
                    'timemodified',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'grade_item' => [
                        'entity' => 'grade_item',
                        'table'  => 'grade_items',
                        'alias'  => 'gi',
                        'on'     => 'gi.id = t.itemid',
                    ],
                ],
            ],

            // ================================================================
            // GRADE CATEGORIES
            // ================================================================
            'grade_category' => [
                'name'   => 'Grade Category',
                'table'  => 'grade_categories',
                'fields' => [
                    'id',
                    'courseid',
                    'parent',
                    'depth',
                    'path',
                    'fullname',
                    'aggregation',
                    'keephigh',
                    'droplow',
                    'aggregateonlygraded',
                    'aggregateoutcomes',
                    'timecreated',
                    'timemodified',
                    'hidden',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                ],
            ],

            // ================================================================
            // QUIZ
            // ================================================================
            'quiz' => [
                'name'   => 'Quiz',
                'table'  => 'quiz',
                'fields' => [
                    'id',
                    'course',
                    'name',
                    'intro',
                    'timeopen',
                    'timeclose',
                    'timelimit',
                    'overduehandling',
                    'graceperiod',
                    'preferredbehaviour',
                    'attempts',
                    'attemptonlast',
                    'grademethod',
                    'decimalpoints',
                    'questiondecimalpoints',
                    'reviewattempt',
                    'grade',
                    'sumgrades',
                    'visible',
                    'timecreated',
                    'timemodified',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.course',
                    ],
                    'quiz_attempts' => [
                        'entity' => 'quiz_attempt',
                        'table'  => 'quiz_attempts',
                        'alias'  => 'qa',
                        'on'     => 'qa.quiz = t.id',
                    ],
                ],
            ],

            // ================================================================
            // QUIZ ATTEMPTS
            // ================================================================
            'quiz_attempt' => [
                'name'   => 'Quiz Attempt',
                'table'  => 'quiz_attempts',
                'fields' => [
                    'id',
                    'quiz',
                    'userid',
                    'attempt',
                    'uniqueid',
                    'layout',
                    'currentpage',
                    'preview',
                    'state',
                    'timestart',
                    'timefinish',
                    'timemodified',
                    'timemodifiedoffline',
                    'timecheckstate',
                    'sumgrades',
                    'gradednotificationsenttime',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'quiz' => [
                        'entity' => 'quiz',
                        'table'  => 'quiz',
                        'alias'  => 'q',
                        'on'     => 'q.id = t.quiz',
                    ],
                ],
            ],

            // ================================================================
            // ASSIGNMENTS
            // ================================================================
            'assign' => [
                'name'   => 'Assignment',
                'table'  => 'assign',
                'fields' => [
                    'id',
                    'course',
                    'name',
                    'intro',
                    'alwaysshowdescription',
                    'nosubmissions',
                    'submissiondrafts',
                    'sendnotifications',
                    'sendlatenotifications',
                    'sendstudentnotifications',
                    'duedate',
                    'allowsubmissionsfromdate',
                    'grade',
                    'timemodified',
                    'completionsubmit',
                    'cutoffdate',
                    'gradingduedate',
                    'teamsubmission',
                    'requireallteammemberssubmit',
                    'teamsubmissiongroupingid',
                    'blindmarking',
                    'hidegrader',
                    'revealidentities',
                    'attemptreopenmethod',
                    'maxattempts',
                    'markingworkflow',
                    'markingallocation',
                    'requiresubmissionstatement',
                    'preventsubmissionnotingroup',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.course',
                    ],
                    'assign_submission' => [
                        'entity' => 'assign_submission',
                        'table'  => 'assign_submission',
                        'alias'  => 'asub',
                        'on'     => 'asub.assignment = t.id',
                    ],
                    'assign_grades' => [
                        'entity' => 'assign_grade',
                        'table'  => 'assign_grades',
                        'alias'  => 'ag',
                        'on'     => 'ag.assignment = t.id',
                    ],
                ],
            ],

            // ================================================================
            // ASSIGNMENT SUBMISSIONS
            // ================================================================
            'assign_submission' => [
                'name'   => 'Assignment Submission',
                'table'  => 'assign_submission',
                'fields' => [
                    'id',
                    'assignment',
                    'userid',
                    'groupid',
                    'attemptnumber',
                    'timecreated',
                    'timemodified',
                    'status',
                    'groupid',
                    'latest',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'assign' => [
                        'entity' => 'assign',
                        'table'  => 'assign',
                        'alias'  => 'a',
                        'on'     => 'a.id = t.assignment',
                    ],
                ],
            ],

            // ================================================================
            // ASSIGNMENT GRADES
            // ================================================================
            'assign_grade' => [
                'name'   => 'Assignment Grade',
                'table'  => 'assign_grades',
                'fields' => [
                    'id',
                    'assignment',
                    'userid',
                    'timecreated',
                    'timemodified',
                    'grader',
                    'grade',
                    'attemptnumber',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'assign' => [
                        'entity' => 'assign',
                        'table'  => 'assign',
                        'alias'  => 'a',
                        'on'     => 'a.id = t.assignment',
                    ],
                ],
            ],

            // ================================================================
            // FORUMS
            // ================================================================
            'forum' => [
                'name'   => 'Forum',
                'table'  => 'forum',
                'fields' => [
                    'id',
                    'course',
                    'type',
                    'name',
                    'intro',
                    'assessed',
                    'assesstimestart',
                    'assesstimefinish',
                    'scale',
                    'grade_forum',
                    'maxbytes',
                    'maxattachments',
                    'forcesubscribe',
                    'trackingtype',
                    'rsstype',
                    'rssarticles',
                    'timemodified',
                    'completiondiscussions',
                    'completionreplies',
                    'completionposts',
                    'displaywordcount',
                    'lockdiscussionafter',
                    'duedate',
                    'cutoffdate',
                    'grade_forum_notify',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.course',
                    ],
                    'forum_discussions' => [
                        'entity' => 'forum_discussion',
                        'table'  => 'forum_discussions',
                        'alias'  => 'fd',
                        'on'     => 'fd.forum = t.id',
                    ],
                ],
            ],

            // ================================================================
            // FORUM DISCUSSIONS
            // ================================================================
            'forum_discussion' => [
                'name'   => 'Forum Discussion',
                'table'  => 'forum_discussions',
                'fields' => [
                    'id',
                    'course',
                    'forum',
                    'name',
                    'firstpost',
                    'userid',
                    'groupid',
                    'assessed',
                    'timemodified',
                    'usermodified',
                    'timestart',
                    'timeend',
                    'pinned',
                    'timelocked',
                ],
                'joins'  => [
                    'forum' => [
                        'entity' => 'forum',
                        'table'  => 'forum',
                        'alias'  => 'f',
                        'on'     => 'f.id = t.forum',
                    ],
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'forum_posts' => [
                        'entity' => 'forum_post',
                        'table'  => 'forum_posts',
                        'alias'  => 'fp',
                        'on'     => 'fp.discussion = t.id',
                    ],
                ],
            ],

            // ================================================================
            // FORUM POSTS
            // ================================================================
            'forum_post' => [
                'name'   => 'Forum Post',
                'table'  => 'forum_posts',
                'fields' => [
                    'id',
                    'discussion',
                    'parent',
                    'userid',
                    'created',
                    'modified',
                    'mailed',
                    'subject',
                    'message',
                    'messageformat',
                    'messagetrust',
                    'attachment',
                    'totalscore',
                    'mailnow',
                    'deleted',
                    'privatereplyto',
                    'wordcount',
                    'charcount',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'forum_discussion' => [
                        'entity' => 'forum_discussion',
                        'table'  => 'forum_discussions',
                        'alias'  => 'fd',
                        'on'     => 'fd.id = t.discussion',
                    ],
                ],
            ],

            // ================================================================
            // COHORTS
            // ================================================================
            'cohort' => [
                'name'   => 'Cohort',
                'table'  => 'cohort',
                'fields' => [
                    'id',
                    'contextid',
                    'name',
                    'idnumber',
                    'description',
                    'descriptionformat',
                    'visible',
                    'component',
                    'timecreated',
                    'timemodified',
                    'theme',
                ],
                'joins'  => [
                    'cohort_members' => [
                        'entity' => 'cohort_member',
                        'table'  => 'cohort_members',
                        'alias'  => 'chm',
                        'on'     => 'chm.cohortid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // COHORT MEMBERS
            // ================================================================
            'cohort_member' => [
                'name'   => 'Cohort Member',
                'table'  => 'cohort_members',
                'fields' => [
                    'id',
                    'cohortid',
                    'userid',
                    'timeadded',
                ],
                'joins'  => [
                    'cohort' => [
                        'entity' => 'cohort',
                        'table'  => 'cohort',
                        'alias'  => 'coh',
                        'on'     => 'coh.id = t.cohortid',
                    ],
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                ],
            ],

            // ================================================================
            // GROUPS
            // ================================================================
            'group' => [
                'name'   => 'Group',
                'table'  => 'groups',
                'fields' => [
                    'id',
                    'courseid',
                    'idnumber',
                    'name',
                    'description',
                    'descriptionformat',
                    'enrolmentkey',
                    'picture',
                    'hidepicture',
                    'timecreated',
                    'timemodified',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                    'group_members' => [
                        'entity' => 'group_member',
                        'table'  => 'groups_members',
                        'alias'  => 'gm',
                        'on'     => 'gm.groupid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // GROUP MEMBERS
            // ================================================================
            'group_member' => [
                'name'   => 'Group Member',
                'table'  => 'groups_members',
                'fields' => [
                    'id',
                    'groupid',
                    'userid',
                    'timeadded',
                    'component',
                    'itemid',
                ],
                'joins'  => [
                    'group' => [
                        'entity' => 'group',
                        'table'  => 'groups',
                        'alias'  => 'g',
                        'on'     => 'g.id = t.groupid',
                    ],
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                ],
            ],

            // ================================================================
            // STANDARD LOG
            // ================================================================
            'log' => [
                'name'   => 'Activity Log',
                'table'  => 'logstore_standard_log',
                'fields' => [
                    'id',
                    'eventname',
                    'component',
                    'action',
                    'target',
                    'objecttable',
                    'objectid',
                    'crud',
                    'edulevel',
                    'contextid',
                    'contextlevel',
                    'contextinstanceid',
                    'userid',
                    'courseid',
                    'relateduserid',
                    'anonymous',
                    'other',
                    'timecreated',
                    'origin',
                    'ip',
                    'realuserid',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                ],
            ],

            // ================================================================
            // USER LAST ACCESS PER COURSE
            // ================================================================
            'user_lastaccess' => [
                'name'   => 'User Last Access (per Course)',
                'table'  => 'user_lastaccess',
                'fields' => [
                    'id',
                    'userid',
                    'courseid',
                    'timeaccess',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                ],
            ],

            // ================================================================
            // BADGES
            // ================================================================
            'badge' => [
                'name'   => 'Badge',
                'table'  => 'badge',
                'fields' => [
                    'id',
                    'name',
                    'description',
                    'timecreated',
                    'timemodified',
                    'usercreated',
                    'usermodified',
                    'issuername',
                    'issuerurl',
                    'issuercontact',
                    'expiredate',
                    'expireperiod',
                    'type',
                    'courseid',
                    'message',
                    'messagesubject',
                    'attachment',
                    'notification',
                    'status',
                    'nextcron',
                    'version',
                    'language',
                    'imageauthorname',
                    'imageauthoremail',
                    'imageauthorurl',
                    'imagecaption',
                ],
                'joins'  => [
                    'badge_issued' => [
                        'entity' => 'badge_issued',
                        'table'  => 'badge_issued',
                        'alias'  => 'bi',
                        'on'     => 'bi.badgeid = t.id',
                    ],
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.courseid',
                    ],
                ],
            ],

            // ================================================================
            // BADGE ISSUED
            // ================================================================
            'badge_issued' => [
                'name'   => 'Badge Issued',
                'table'  => 'badge_issued',
                'fields' => [
                    'id',
                    'badgeid',
                    'userid',
                    'uniquehash',
                    'dateissued',
                    'dateexpire',
                    'visible',
                    'issuernotified',
                ],
                'joins'  => [
                    'badge' => [
                        'entity' => 'badge',
                        'table'  => 'badge',
                        'alias'  => 'b',
                        'on'     => 'b.id = t.badgeid',
                    ],
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                ],
            ],

            // ================================================================
            // COMPETENCIES
            // ================================================================
            'competency' => [
                'name'   => 'Competency',
                'table'  => 'competency',
                'fields' => [
                    'id',
                    'shortname',
                    'description',
                    'descriptionformat',
                    'idnumber',
                    'competencyframeworkid',
                    'parentid',
                    'path',
                    'sortorder',
                    'ruletype',
                    'ruleoutcome',
                    'ruleconfig',
                    'scaleid',
                    'scaleconfiguration',
                    'timecreated',
                    'timemodified',
                    'usermodified',
                ],
                'joins'  => [
                    'user_competency' => [
                        'entity' => 'user_competency',
                        'table'  => 'competency_usercomp',
                        'alias'  => 'uc',
                        'on'     => 'uc.competencyid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // USER COMPETENCIES
            // ================================================================
            'user_competency' => [
                'name'   => 'User Competency',
                'table'  => 'competency_usercomp',
                'fields' => [
                    'id',
                    'userid',
                    'competencyid',
                    'status',
                    'reviewerid',
                    'proficiency',
                    'grade',
                    'timecreated',
                    'timemodified',
                    'usermodified',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'competency' => [
                        'entity' => 'competency',
                        'table'  => 'competency',
                        'alias'  => 'comp',
                        'on'     => 'comp.id = t.competencyid',
                    ],
                ],
            ],

            // ================================================================
            // MESSAGES
            // ================================================================
            'message' => [
                'name'   => 'Message',
                'table'  => 'messages',
                'fields' => [
                    'id',
                    'useridfrom',
                    'conversationid',
                    'subject',
                    'fullmessage',
                    'fullmessageformat',
                    'fullmessagehtml',
                    'smallmessage',
                    'timecreated',
                    'customdata',
                ],
                'joins'  => [
                    'user_from' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'uf',
                        'on'     => 'uf.id = t.useridfrom',
                    ],
                ],
            ],

            // ================================================================
            // SCORM
            // ================================================================
            'scorm' => [
                'name'   => 'SCORM Activity',
                'table'  => 'scorm',
                'fields' => [
                    'id',
                    'course',
                    'name',
                    'intro',
                    'scormtype',
                    'reference',
                    'version',
                    'maxgrade',
                    'grademethod',
                    'whatgrade',
                    'maxattempt',
                    'forcecompleted',
                    'forcenewattempt',
                    'lastattemptlock',
                    'displayattemptstatus',
                    'displaycoursestructure',
                    'updatefreq',
                    'sha1hash',
                    'md5hash',
                    'revision',
                    'launch',
                    'skipview',
                    'hidebrowse',
                    'hidetoc',
                    'nav',
                    'navpositionleft',
                    'navpositiontop',
                    'auto',
                    'popup',
                    'options',
                    'width',
                    'height',
                    'timeopen',
                    'timeclose',
                    'timemodified',
                    'completionstatusrequired',
                    'completionscorerequired',
                    'completionstatusallscos',
                ],
                'joins'  => [
                    'course' => [
                        'entity' => 'course',
                        'table'  => 'course',
                        'alias'  => 'c',
                        'on'     => 'c.id = t.course',
                    ],
                    'scorm_scoes_track' => [
                        'entity' => 'scorm_track',
                        'table'  => 'scorm_scoes_track',
                        'alias'  => 'sst',
                        'on'     => 'sst.scormid = t.id',
                    ],
                ],
            ],

            // ================================================================
            // SCORM TRACKING
            // ================================================================
            'scorm_track' => [
                'name'   => 'SCORM Tracking',
                'table'  => 'scorm_scoes_track',
                'fields' => [
                    'id',
                    'userid',
                    'scormid',
                    'scoid',
                    'attempt',
                    'element',
                    'value',
                    'timemodified',
                ],
                'joins'  => [
                    'user' => [
                        'entity' => 'user',
                        'table'  => 'user',
                        'alias'  => 'u',
                        'on'     => 'u.id = t.userid',
                    ],
                    'scorm' => [
                        'entity' => 'scorm',
                        'table'  => 'scorm',
                        'alias'  => 's',
                        'on'     => 's.id = t.scormid',
                    ],
                ],
            ],

        ];
    }
}
