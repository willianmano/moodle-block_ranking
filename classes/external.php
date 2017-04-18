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
 * Ranking external functions and service definitions.
 *
 * @package    block_ranking
 * @copyright  2016 J. Kalkhof <jerry@ccadapps.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.1
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/blocks/ranking/lib.php');

/**
 * Ranking external functions and service definitions class
 *
 * @copyright 2017 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class blocks_ranking_external extends external_api {

    /**
     * Describes the parameters for get_databases_by_courses.
     *
     * @return external_external_function_parameters
     * @since Moodle 2.9
     */
    public static function get_ranking_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id', VALUE_REQUIRED),
                    'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of databases in a provided list of courses,
     * if no list is provided all databases that the user can view will be returned.
     *
     * @param array $courseids the course ids
     * @return array the database details
     * @since Moodle 2.9
     */
    public static function get_ranking($courseids = array()) {
        global $DB, $COURSE;

        $params = self::validate_parameters(self::get_ranking_parameters(), array('courseids' => $courseids));
        $warnings = array();

        $mycourses = array();

        // Array to store the databases to return.
        $arrdatabases = array();

        $studentlist = array();

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {
            foreach ($params['courseids'] as $courseid) {
                // Code get from report.php .
                $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

                require_login($courseid);
                $context = context_course::instance($courseid);

                $perpage = 100;
                $group = null;

                $userfields = user_picture::fields('u', array('username'));
                $from = "FROM {user} u
                        INNER JOIN {role_assignments} a ON a.userid = u.id
                        LEFT JOIN {ranking_points} r ON r.userid = u.id AND r.courseid = :r_courseid
                        INNER JOIN {context} c ON c.id = a.contextid";

                $where = "WHERE a.contextid = :contextid
                        AND a.userid = u.id
                        AND a.roleid = :roleid
                        AND c.instanceid = :courseid";

                $params['contextid'] = $context->id;
                $params['roleid'] = 5;
                $params['courseid'] = $course->id;
                $params['r_courseid'] = $params['courseid'];

                $order = "ORDER BY r.points DESC, u.firstname ASC
                        LIMIT " . $perpage;

                if ($group) {
                    $from .= " INNER JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :groupid";
                    $params['groupid'] = $group;
                }

                $sql = "SELECT $userfields, r.points $from $where $order";

                $students = array_values($DB->get_records_sql($sql, $params));

                // Code get from lib.php : generate_table .
                $data = $students;
                $lastpos = 1;
                $lastpoints = current($data)->points;
                for ($i = 0; $i < count($data); $i++) {
                    if ($lastpoints > $data[$i]->points) {
                        $lastpos++;
                        $lastpoints = $data[$i]->points;
                    }

                    // Prepare to get user icon url.
                    $userid = $data[$i]->id;
                    $context = context_user::instance($userid);
                    $contextid = $context->id;
                    $image = null;

                    $url = moodle_url::make_pluginfile_url($contextid, 'user', 'icon', null, '/', $image);

                    // Position, picture, name, points.
                    $row = array(
                        "position" => $lastpos,
                        "picture" => urlencode($url),
                        "name" => $data[$i]->firstname,
                        "points" => $data[$i]->points ?: 0
                    );

                    $studentlist[] = $row;
                }
            }
        }

        $result = array();
        $result['leaderboard'] = $studentlist;
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Describes the get_databases_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 2.9
     */
    public static function get_ranking_returns() {
        return new external_single_structure(
            array(
                'leaderboard' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'position' => new external_value(PARAM_INT, 'position'),
                            'picture' => new external_value(PARAM_TEXT, 'picture url'),
                            'name' => new external_value(PARAM_RAW, 'name'),
                            'points' => new external_value(PARAM_FLOAT, 'points')
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }
}
