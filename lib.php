<?php
// This file is part of Ranking block for Moodle - http://moodle.org/
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
 * Ranking block definition
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define ('DEFAULT_POINTS', 2);

// Store the courses contexts.
$coursescontexts = array();

/**
 * Return the list of students in the course ranking
 *
 * @param int
 * @return mixed
 */
function block_ranking_get_students($limit = null) {
    global $COURSE, $DB, $PAGE;

    // Get block ranking configuration.
    $cfgranking = get_config('block_ranking');

    // Get limit from default configuration or instance configuration.
    if (!$limit) {
        if (isset($cfgranking->rankingsize) && trim($cfgranking->rankingsize) != '') {
            $limit = $cfgranking->rankingsize;
        } else {
            $limit = 10;
        }
    }

    $context = $PAGE->context;

    $userfields = user_picture::fields('u', array('username'));
    $sql = "SELECT
                DISTINCT $userfields, r.points
            FROM
                {user} u
            INNER JOIN {role_assignments} a ON a.userid = u.id
            INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :r_courseid
            INNER JOIN {context} c ON c.id = a.contextid
            WHERE a.contextid = :contextid
            AND a.userid = u.id
            AND a.roleid = :roleid
            AND c.instanceid = :courseid
            AND r.courseid = :crsid
            ORDER BY r.points DESC, u.firstname ASC
            LIMIT " . $limit;
    $params['contextid'] = $context->id;
    $params['roleid'] = 5;
    $params['courseid'] = $COURSE->id;
    $params['crsid'] = $COURSE->id;
    $params['r_courseid'] = $COURSE->id;

    $users = array_values($DB->get_records_sql($sql, $params));

    return $users;
}

/**
 * Get the students points based on a time interval
 *
 * @param int
 * @param int
 * @param int
 * @return mixed
 */
function block_ranking_get_students_by_date($limit = null, $datestart, $dateend) {
    global $COURSE, $DB, $PAGE;

    // Get block ranking configuration.
    $cfgranking = get_config('block_ranking');

    // Get limit from default configuration or instance configuration.
    if (!$limit) {
        if (isset($cfgranking->rankingsize) && trim($cfgranking->rankingsize) != '') {
            $limit = $cfgranking->rankingsize;
        } else {
            $limit = 10;
        }
    }

    $context = $PAGE->context;

    $userfields = user_picture::fields('u', array('username'));
    $sql = "SELECT
                DISTINCT $userfields,
                sum(rl.points) as points
            FROM
                {user} u
            INNER JOIN {role_assignments} a ON a.userid = u.id
            INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :r_courseid
            INNER JOIN {ranking_logs} rl ON rl.rankingid = r.id
            INNER JOIN {context} c ON c.id = a.contextid
            WHERE a.contextid = :contextid
            AND a.userid = u.id
            AND a.roleid = :roleid
            AND c.instanceid = :courseid
            AND r.courseid = :crsid
            AND rl.timecreated BETWEEN :weekstart AND :weekend
            GROUP BY u.id
            ORDER BY points DESC, u.firstname ASC
            LIMIT " . $limit;

    $params['contextid'] = $context->id;
    $params['roleid'] = 5;
    $params['courseid'] = $COURSE->id;
    $params['r_courseid'] = $COURSE->id;
    $params['crsid'] = $COURSE->id;
    $params['weekstart'] = $datestart;
    $params['weekend'] = $dateend;

    $users = array_values($DB->get_records_sql($sql, $params));

    return $users;
}

/**
 * Build the ranking table to be viewd in the course
 * @param array $rankinglastmonth List of students of the last month ranking
 * @param array $rankinglastweek List of students of the last week ranking
 * @param array $rankinggeral List of students to be print in ranking block
 * @return string
 */
function block_ranking_print_students($rankinglastmonth, $rankinglastweek, $rankinggeral) {
    global $PAGE;

    $tablelastweek = generate_table($rankinglastweek);
    $tablelastmonth = generate_table($rankinglastmonth);
    $tablegeral = generate_table($rankinggeral);

    $PAGE->requires->js_init_call('M.block_ranking.init_tabview');

    return '<div id="ranking-tabs">
                <ul>
                    <li><a href="#semanal">'.get_string('weekly', 'block_ranking').'</a></li>
                    <li><a href="#mensal">'.get_string('monthly', 'block_ranking').'</a></li>
                    <li><a href="#geral">'.get_string('general', 'block_ranking').'</a></li>
                </ul>
                <div>
                    <div id="semanal">'.$tablelastweek.'</div>
                    <div id="mensal">'.$tablelastmonth.'</div>
                    <div id="geral">'.$tablegeral.'</div>
                </div>
            </div>';
}

/**
 * Print the student individual ranking points
 *
 * @return string
 */
function block_ranking_print_individual_ranking() {
    global $USER, $COURSE, $PAGE;

    if (!is_student($PAGE->context, $USER->id)) {
        return '';
    }

    $weekstart = strtotime(date('d-m-Y', strtotime('-'.date('w').' days')));
    $lastweekpoints = block_ranking_get_student_points_by_date($USER->id, $weekstart, time());
    $lastweekpoints = $lastweekpoints->points != null ? $lastweekpoints->points : '0';
    $lastweekpoints = $lastweekpoints . " " . strtolower(get_string('table_points', 'block_ranking'));

    $monthstart = strtotime(date('Y-m-01'));
    $lastmonthpoints = block_ranking_get_student_points_by_date($USER->id, $monthstart, time());
    $lastmonthpoints = $lastmonthpoints->points != null ? $lastmonthpoints->points : '0';
    $lastmonthpoints = $lastmonthpoints . " " . strtolower(get_string('table_points', 'block_ranking'));

    $totalpoints = block_ranking_get_student_points($USER->id);
    $totalpoints = $totalpoints->points != null ? $totalpoints->points : '0';
    $totalpoints = $totalpoints . " " . strtolower(get_string('table_points', 'block_ranking'));

    $table = new html_table();
    $table->attributes = array("class" => "rankingTable table table-striped generaltable");
    $table->head = array(
                        get_string('weekly', 'block_ranking'),
                        get_string('monthly', 'block_ranking'),
                        get_string('general', 'block_ranking')
                    );

    $row = new html_table_row();
    $row->cells = array($lastweekpoints, $lastmonthpoints, $totalpoints);
    $table->data[] = $row;

    $individualranking = html_writer::table($table);

    return "<h4>".get_string('your_score', 'block_ranking').":</h4>" . $individualranking;
}

/**
 * Get the student points
 *
 * @param int
 * @return mixed
 */
function block_ranking_get_student_points($userid) {
    global $COURSE, $DB;

    $sql = "SELECT
                sum(rl.points) as points
            FROM
                {user} u
            INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :courseid
            INNER JOIN {ranking_logs} rl ON rl.rankingid = r.id
            WHERE u.id = :userid
            AND r.courseid = :crsid";

    $params['userid'] = $userid;
    $params['courseid'] = $COURSE->id;
    $params['crsid'] = $COURSE->id;

    return $DB->get_record_sql($sql, $params);
}

/**
 * Get the student points based on a time interval
 *
 * @param int
 * @param int
 * @param int
 * @return mixed
 */
function block_ranking_get_student_points_by_date($userid, $datestart, $dateend) {
    global $COURSE, $DB;

    $sql = "SELECT
                sum(rl.points) as points
            FROM
                {user} u
            INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :courseid
            INNER JOIN {ranking_logs} rl ON rl.rankingid = r.id
            WHERE u.id = :userid
            AND r.courseid = :crsid
            AND rl.timecreated BETWEEN :weekstart AND :weekend";

    $params['userid'] = $userid;
    $params['courseid'] = $COURSE->id;
    $params['crsid'] = $COURSE->id;
    $params['weekstart'] = $datestart;
    $params['weekend'] = $dateend;

    return $DB->get_record_sql($sql, $params);
}

/**
 * Return a table of ranking based on data passed
 *
 * @param mixed
 * @return mixed
 */
function generate_table($data) {
    global $USER, $OUTPUT;

    if (empty($data)) {
        return get_string('nostudents', 'block_ranking');
    }

    $table = new html_table();
    $table->attributes = array("class" => "rankingTable table table-striped generaltable");
    $table->head = array(
                        get_string('table_position', 'block_ranking'),
                        get_string('table_name', 'block_ranking'),
                        get_string('table_points', 'block_ranking')
                    );
    $lastpos = 1;
    $lastpoints = current($data)->points;
    for ($i = 0; $i < count($data); $i++) {
        $row = new html_table_row();

        // Verify if the logged user is one user in ranking.
        if ($data[$i]->id == $USER->id) {
            $row->attributes = array('class' => 'itsme');
        }

        if ($lastpoints > $data[$i]->points) {
            $lastpos++;
            $lastpoints = $data[$i]->points;
        }

        $row->cells = array(
                        $lastpos,
                        $OUTPUT->user_picture($data[$i], array('size' => 24, 'alttext' => false)) . ' '.$data[$i]->firstname,
                        $data[$i]->points ?: '-'
                    );
        $table->data[] = $row;
    }

    return html_writer::table($table);
}

/**
 * Returns the modules completions
 *
 * @param int
 * @return mixed
 */
function get_modules_completion($lastcomputedid) {
    global $DB;

    $sql = "SELECT
                cmc.*,
                cmc.id as cmcid,
                cm.course,
                cm.module as moduleid,
                cm.instance,
                cm.score,
                cm.indent,
                cm.completion,
                cm.completiongradeitemnumber,
                cm.completionview,
                cm.completionexpected,
                m.name as modulename
            FROM
                {course_modules_completion} cmc
            INNER JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
            INNER JOIN {modules} m ON m.id = cm.module
            WHERE
                cmc.completionstate = 1
                AND cmc.id > :lastcomputedid
            ORDER BY cmc.id";

    $params['lastcomputedid'] = $lastcomputedid;

    $completedmodules = array_values($DB->get_records_sql($sql, $params));

    return $completedmodules;
}

/**
 * Function executed by cron to calculate the student points
 *
 * @return void
 */
function block_ranking_calculate_points() {
    global $DB;

    $criteria = array(
        'plugin' => 'block_ranking',
        'name' => 'lastcomputedid'
    );

    $lastcomputedid = current($DB->get_records('config_plugins', $criteria));

    $completedmodules = get_modules_completion((int)$lastcomputedid->value);

    if (!empty($completedmodules)) {
        foreach ($completedmodules as $key => $usercompletion) {

            $coursecontext = get_context_course($usercompletion->course);

            if (is_student($coursecontext, $usercompletion->userid)) {
                add_point_to_user($usercompletion);
            }

        }

        $lastid = end($completedmodules);

        $lastcomputedid->value = $lastid->cmcid;

        $DB->update_record('config_plugins', $lastcomputedid);

        mtrace('... points computeds :P');
    } else {
        mtrace('... No new points to be computed');
    }
}

/**
 * Add points to users
 *
 * @return void
 */
function add_point_to_user($usercompletion) {
    // Get block ranking configuration.
    $cfgranking = get_config('block_ranking');

    switch ($usercompletion->modulename) {
        case 'assign':
            add_default_points($usercompletion, $cfgranking->assignpoints);
        break;

        case 'resource':
            add_default_points($usercompletion, $cfgranking->resourcepoints);
        break;

        case 'forum':
            add_default_points($usercompletion, $cfgranking->forumpoints);
        break;

        case 'workshop':
            add_default_points($usercompletion, $cfgranking->workshoppoints);
        break;

        case 'page':
            add_default_points($usercompletion, $cfgranking->pagepoints);
        break;

        default:
            add_default_points($usercompletion, $cfgranking->defaultpoints);
        break;
    }
}

/**
 * Default method to add points to students
 *
 * @return void
 */
function add_default_points($usercompletion, $points) {
    if (!isset($points) || trim($points) != '') {
        $points = DEFAULT_POINTS;
    }
    if (!is_null($usercompletion->completiongradeitemnumber)) {
        $activitygrade = get_activity_finalgrade($usercompletion->modulename, $usercompletion->instance, $usercompletion->userid);
        $points += $activitygrade;
    }

    $rankingid = add_or_update_user_points($usercompletion->userid, $usercompletion->course, $points);
    add_ranking_log($rankingid, $usercompletion->course, $usercompletion->id, $points);
}

/**
 * Save students points
 *
 * @param int
 * @param int
 * @param int
 * @return int
 */
function add_or_update_user_points($userid, $courseid, $points) {
    global $DB;

    $sql = "SELECT * FROM {ranking_points}
            WHERE userid = :userid AND courseid = :courseid";
    $params['userid'] = $userid;
    $params['courseid'] = $courseid;

    $userpoints = current($DB->get_records_sql($sql, $params));

    // User dont have points yet.
    if (empty($userpoints)) {
        // Basic block configuration.
        $userpoints = new stdClass();
        $userpoints->userid = $userid;
        $userpoints->courseid = $courseid;
        $userpoints->points = $points;
        $userpoints->timecreated = time();
        $userpoints->timemodified = time();

        $rankingid = $DB->insert_record('ranking_points', $userpoints, true);
    } else {
        $userpoints->points = $userpoints->points + $points;
        $userpoints->timemodified = time();
        $DB->update_record('ranking_points', $userpoints);
        $rankingid = $userpoints->id;
    }
    return $rankingid;
}

/**
 * Add points movement to log
 *
 * @param int
 * @param int
 * @param int
 * @param int
 * @return int
 */
function add_ranking_log($rankingid, $courseid, $cmc, $points) {
    global $DB;

    $rankinglog = new stdClass();
    $rankinglog->rankingid = $rankingid;
    $rankinglog->courseid = $courseid;
    $rankinglog->course_modules_completion = $cmc;
    $rankinglog->points = $points;
    $rankinglog->timecreated = time();

    $logid = $DB->insert_record('ranking_logs', $rankinglog, true);

    return $logid;
}

/**
 * Returns activity grade
 *
 * @param int
 * @param int
 * @param int
 * @return float
 */
function get_activity_finalgrade($activity, $activityid, $userid) {
    global $DB;

    $sql = "SELECT
                gg.itemid, gg.userid, gg.rawscaleid, gg.finalgrade, gi.scaleid
            FROM
                {grade_grades} gg
            INNER JOIN {grade_items} gi ON gi.id = gg.itemid
            WHERE gi.itemmodule = :activity AND gi.iteminstance = :iteminstance AND gg.userid = :userid";
    $params['activity'] = $activity;
    $params['iteminstance'] = $activityid;
    $params['userid'] = $userid;

    $gradeitem = $DB->get_records_sql($sql, $params);

    $finalgrade = 0;
    if (!empty($gradeitem)) {
        $gradeitem = current($gradeitem);

        // Grade without scale -- grademax 100.
        if (empty($gradeitem->scaleid)) {
            $finalgrade = $gradeitem->finalgrade;

            if ($finalgrade > 10) {
                $finalgrade = $finalgrade / 10;
            }
        } else {
            $finalgrade = get_finalgrade_by_scale($gradeitem->finalgrade, $gradeitem->scaleid);
        }
    }

    return $finalgrade;
}

/**
 * Returns activity grade by scale
 *
 * @param int
 * @param int
 * @return int
 */
function get_finalgrade_by_scale($finalgrade, $scaleid) {
    global $DB;

    $sql = "SELECT scale FROM {scale} WHERE id = :scaleid";
    $params['scaleid'] = $scaleid;

    $scale = $DB->get_records_sql($sql, $params);

    if (!empty($scale)) {
        $scale = current($scale);
        $scale = explode(',', $scale->scale);

        $finalgrade = $scale[$finalgrade - 1];
    }

    return $finalgrade;
}

/**
 * Get the course context
 *
 * @param int
 * @return mixed
 */

function get_context_course($courseid) {
    global $coursescontexts;

    if (!is_null($coursescontexts) && array_key_exists($courseid, $coursescontexts)) {
        return $coursescontexts[$courseid];
    }

    $coursescontexts[$courseid] = context_course::instance($courseid);

    return $coursescontexts[$courseid];
}

/**
 * Verify if the user is a student
 *
 * @param int
 * @param int
 * @return bool
 */
function is_student($context, $userid) {
    $userroles = get_user_roles($context, $userid);

    $isstudent = false;
    if (!empty($userroles)) {
        foreach ($userroles as $r => $role) {
            if ($role->roleid == 5) {
                $isstudent = true;
                break;
            }
        }
    }

    return $isstudent;
}
