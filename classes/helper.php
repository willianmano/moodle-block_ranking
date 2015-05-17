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
 * Ranking block helper
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block ranking helper class.
 *
 * @package    block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ranking_helper {

    /**
     * Observe the events, and dispatch them if necessary.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function observer(\core\event\base $event) {

        if (!self::is_completion_completed($event->objectid)) {
            return;
        }

        if (self::is_completion_repeated($event->courseid, $event->userid, $event->objectid)) {
            return;
        }

        block_ranking_manager::add_user_points($event->objectid);
    }

    /**
     * Verify if the completion is completed
     *
     * @param int $cmcid
     *
     * @return boolean
     */
    protected static function is_completion_completed($cmcid) {
        global $DB;

        $cmc = $DB->get_record('course_modules_completion', array('id' => $cmcid), '*');

        return (bool) $cmc->completionstate;
    }

    /**
     * Verify if the student already receives points for the completion before
     *
     * @param int $courseid
     * @param int $userid
     * @param int $cmcid
     *
     * @return mixed
     */
    protected static function is_completion_repeated($courseid, $userid, $cmcid) {
        global $DB;

        $sql = "SELECT
                 *
                FROM {ranking_points} p
                INNER JOIN {ranking_logs} l ON l.rankingid = p.id
                WHERE p.courseid = :courseid
                AND p.userid = :userid
                AND l.course_modules_completion = :cmcid";

        $params['courseid'] = $courseid;
        $params['userid'] = $userid;
        $params['cmcid'] = $cmcid;

        return $DB->get_record_sql($sql, $params);
    }

}
