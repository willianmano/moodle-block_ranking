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
 * Block ranking lib.
 *
 * @package   block_ranking
 * @copyright 2018 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_ranking;

defined('MOODLE_INTERNAL') || die();

use user_picture;
use context_course;

/**
 * Zegna ranking main utillity class
 *
 * @package   block_ranking
 * @copyright 2018 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rankinglib {
    /**
     * Get the students points based on a time interval
     *
     * @param stdClass $course
     * @param context_course $context
     * @param int $datestart
     * @param int $dateend
     * @param int $rankingsize
     *
     * @return mixed
     */
    public static function get_ranking_by_date($course, context_course $context, $datestart, $dateend, $rankingsize) {
        global $DB;

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
            LIMIT " . $rankingsize;

        $params['contextid'] = $context->id;
        $params['roleid'] = 5;
        $params['courseid'] = $course->id;
        $params['r_courseid'] = $course->id;
        $params['crsid'] = $course->id;
        $params['weekstart'] = $datestart;
        $params['weekend'] = $dateend;

        $users = array_values($DB->get_records_sql($sql, $params));

        return $users;
    }

    /**
     * Return the general students course ranking
     *
     * @param stdClass $course
     * @param context_course $context
     * @param int $rankingsize
     *
     * @return mixed
     */
    public static function get_ranking_general($course, context_course $context, $rankingsize) {
        global $DB;

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
            LIMIT " . $rankingsize;

        $params['contextid'] = $context->id;
        $params['roleid'] = 5;
        $params['courseid'] = $course->id;
        $params['crsid'] = $course->id;
        $params['r_courseid'] = $course->id;

        $users = array_values($DB->get_records_sql($sql, $params));

        return $users;
    }

    /**
     * Return a individual user points
     *
     * @param $courseid
     * @param $weekstart
     * @param $monthstart
     *
     * @return array
     */
    public static function get_ranking_individual($courseid, $weekstart, $monthstart) {
        global $USER;

        if (!user_has_role_assignment($USER->id, 5)) {
            return '';
        }

        $lastweekpoints = self::get_student_points_by_date($USER->id, $courseid, $weekstart, time());
        $lastweekpoints = $lastweekpoints->points != null ? $lastweekpoints->points : '0';

        $lastmonthpoints = self::get_student_points_by_date($USER->id, $courseid, $monthstart, time());
        $lastmonthpoints = $lastmonthpoints->points != null ? $lastmonthpoints->points : '0';

        $totalpoints = self::get_student_points($USER->id, $courseid);
        $totalpoints = $totalpoints->points != null ? $totalpoints->points : '0';

        return [
            'lastweek' => $lastweekpoints,
            'lastmonth' => $lastmonthpoints,
            'totalpoints' => $totalpoints
        ];
    }

    /**
     * Get the student points
     *
     * @param int $userid
     * @param int $courseid
     *
     * @return mixed
     */
    public static function get_student_points($userid, $courseid) {
        global $DB;

        $sql = "SELECT
                sum(rl.points) as points
            FROM
                {user} u
            INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :courseid
            INNER JOIN {ranking_logs} rl ON rl.rankingid = r.id
            WHERE u.id = :userid
            AND r.courseid = :crsid";

        $params['userid'] = $userid;
        $params['courseid'] = $courseid;
        $params['crsid'] = $courseid;

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get the student points based on a time interval
     *
     * @param int $userid
     * @param int $courseid
     * @param int $datestart
     * @param int $dateend
     *
     * @return mixed
     */
    public static function get_student_points_by_date($userid, $courseid, $datestart, $dateend) {
        global $DB;

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
        $params['courseid'] = $courseid;
        $params['crsid'] = $courseid;
        $params['weekstart'] = $datestart;
        $params['weekend'] = $dateend;

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Return the full ranking of a course
     *
     * @param $courseid
     * @param $contextid
     * @param $perpage
     * @param null $groupid
     *
     * @return array
     */
    public static function get_ranking_general_report($courseid, $contextid, $perpage, $groupid = null) {
        global $DB;

        $userfields = user_picture::fields('u', array('username'));

        $from = "FROM {user} u
                INNER JOIN {role_assignments} a ON a.userid = u.id
                LEFT JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :r_courseid
                INNER JOIN {context} c ON c.id = a.contextid";

        $where = "WHERE a.contextid = :contextid
                AND a.userid = u.id
                AND a.roleid = :roleid
                AND c.instanceid = :courseid";

        $params['contextid'] = $contextid;
        $params['roleid'] = 5;
        $params['courseid'] = $courseid;
        $params['r_courseid'] = $params['courseid'];

        $order = "ORDER BY r.points DESC, u.firstname ASC
        LIMIT " . $perpage;

        if ($groupid) {
            $from .= " INNER JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :groupid";
            $params['groupid'] = $groupid;
        }

        $sql = "SELECT {$userfields}, r.points {$from } {$where } {$order}";

        return array_values($DB->get_records_sql($sql, $params));
    }

    public static function prepare_ranking_data($data = null) {
        global $USER, $OUTPUT;

        if (!$data) {
            return null;
        }

        $lastpos = 1;
        $lastpoints = current($data)->points;
        $returndata = [];
        for ($i = 0; $i < count($data); $i++) {
            // Verify if the logged user is present in ranking.
            $class = '';
            if ($data[$i]->id == $USER->id) {
                $class = 'itsme';
            }

            if ($lastpoints > $data[$i]->points) {
                $lastpos++;
                $lastpoints = $data[$i]->points;
            }

            $returndata[$i] = [
                'pos' => $lastpos,
                'user' => $OUTPUT->user_picture($data[$i], array('size' => 24, 'alttext' => false)) . ' '.$data[$i]->firstname,
                'points' => $data[$i]->points ?: '-',
                'class' => $class
            ];
        }

        return $returndata;
    }
}