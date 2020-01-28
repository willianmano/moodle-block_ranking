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
 * Ranking block student lib
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_ranking;

defined('MOODLE_INTERNAL') || die();

/**
 * Ranking block student lib class.
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class studentlib {
    /**
     * Returns the total user's points in a course
     *
     * @return mixed
     *
     * @throws \dml_exception
     */
    public function get_total_course_points() {
        global $DB, $COURSE, $USER;

        $sql = "SELECT
                    sum(rl.points) as points
                FROM
                    {user} u
                INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :courseid
                INNER JOIN {ranking_logs} rl ON rl.rankingid = r.id
                WHERE u.id = :userid
                AND r.courseid = :crsid";

        $params['userid'] = $USER->id;
        $params['courseid'] = $COURSE->id;
        $params['crsid'] = $COURSE->id;

        $points = $DB->get_record_sql($sql, $params);

        if ($points) {
            return $points->points;
        }

        return 0;
    }

    /**
     * Returns the total user's points in a course in a period
     *
     * @param int $datestart
     * @param int $dateend
     *
     * @return mixed
     *
     * @throws \dml_exception
     */
    public function get_student_points_by_date($datestart, $dateend) {
        global $DB, $COURSE, $USER;

        $sql = "SELECT
                    sum(rl.points) as points
                FROM
                    {user} u
                INNER JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :courseid
                INNER JOIN {ranking_logs} rl ON rl.rankingid = r.id
                WHERE u.id = :userid
                AND r.courseid = :crsid
                AND rl.timecreated BETWEEN :weekstart AND :weekend";

        $params['userid'] = $USER->id;
        $params['courseid'] = $COURSE->id;
        $params['crsid'] = $COURSE->id;
        $params['weekstart'] = $datestart;
        $params['weekend'] = $dateend;

        $points = $DB->get_record_sql($sql, $params);

        if ($points) {
            return $points->points;
        }

        return 0;
    }

    /**
     * Checks if the user is enrolled as student in the course.
     *
     * @return bool
     */
    public function is_student() {
        global $COURSE, $USER;

        $context = \context_course::instance($COURSE->id);

        return user_has_role_assignment($USER->id, 5, $context->id);
    }
}
