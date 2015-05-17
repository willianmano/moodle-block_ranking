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
    global $USER, $COURSE;

    if (!is_student($USER->id)) {
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
 * Verify if the user is a student
 *
 * @param int
 * @param int
 * @return bool
 */
function is_student($userid) {
    return user_has_role_assignment($userid, 5);
}
