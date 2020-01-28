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
 * Ranking block lib
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_ranking;

defined('MOODLE_INTERNAL') || die();

use user_picture;

/**
 * Ranking block lib class.
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rankinglib {
    /**
     * Return the list of students in the course ranking
     *
     * @param int $limit
     * @param int $groupid
     *
     * @return mixed
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_students($limit = 10, $groupid = null) {
        global $COURSE, $DB, $PAGE;

        $context = $PAGE->context;

        $userfields = user_picture::fields('u', array('username'));
        $sql = "SELECT
                DISTINCT $userfields, r.points
            FROM
                {user} u
            INNER JOIN {role_assignments} a ON a.userid = u.id
            INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :r_courseid
            INNER JOIN {context} c ON c.id = a.contextid";

        $params = [
            'contextid' => $context->id,
            'roleid' => 5,
            'courseid' => $COURSE->id,
            'crsid' => $COURSE->id,
            'r_courseid' => $COURSE->id
        ];

        if ($groupid) {
            $sql .= " INNER JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :groupid";

            $params['groupid'] = $groupid;
        }

        $sql .= " WHERE a.contextid = :contextid
            AND a.userid = u.id
            AND a.roleid = :roleid
            AND c.instanceid = :courseid
            AND r.courseid = :crsid
            ORDER BY r.points DESC, u.firstname ASC";

        $users = array_values($DB->get_records_sql($sql, $params, 0, $limit));

        return $this->get_aditionaldata($users);
    }

    /**
     * Get the students points based on a time interval
     *
     * @param int $datestart
     * @param int $dateend
     * @param int $limit
     *
     * @return mixed
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_students_by_date($datestart, $dateend, $limit = 10) {
        global $COURSE, $DB, $PAGE;

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
            AND rl.timecreated BETWEEN :datestart AND :dateend
            GROUP BY " . $userfields . "
            ORDER BY points DESC, u.firstname ASC";

        $params['contextid'] = $context->id;
        $params['roleid'] = 5;
        $params['courseid'] = $COURSE->id;
        $params['r_courseid'] = $COURSE->id;
        $params['crsid'] = $COURSE->id;
        $params['datestart'] = $datestart;
        $params['dateend'] = $dateend;

        $users = array_values($DB->get_records_sql($sql, $params, 0, $limit));

        return $this->get_aditionaldata($users);
    }

    /**
     * Get the users aditional data.
     *
     * @param array $data
     *
     * @return string|array
     *
     * @throws \coding_exception
     */
    protected function get_aditionaldata($data) {
        global $USER, $OUTPUT;

        if (empty($data)) {
            return get_string('nostudents', 'block_ranking');
        }

        $lastpos = 1;
        $lastpoints = current($data)->points;
        for ($i = 0; $i < count($data); $i++) {

            $data[$i]->class = 'table-default';
            // Verify if the logged user is one user in ranking.
            if ($data[$i]->id == $USER->id) {
                $data[$i]->class = 'table-success';
            }

            if ($lastpoints > $data[$i]->points) {
                $lastpos++;
                $lastpoints = $data[$i]->points;
            }

            $data[$i]->position = $lastpos;
            $data[$i]->userpic = $OUTPUT->user_picture($data[$i], array('size' => 24, 'alttext' => false));
        }

        return $data;
    }
}
