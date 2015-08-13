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
        global $DB;


        if (!self::is_student($event->relateduserid)) {
            return;
        }

        if ($event->eventname == '\mod_quiz\event\attempt_submitted') {

            $enablemultipleattempts = $DB->get_record('config_plugins', array('plugin' => 'block_ranking', 'name' => 'enable_multiple_quizz_attempts'));

            if (isset($enablemultipleattempts) && $enablemultipleattempts->value == 0) {
                $isrepeated = self::is_completion_repeated($event->courseid, $event->relateduserid, $event->contextinstanceid);
                
                if ($isrepeated) {
                    return;
                }
            }

            $objectid = self::get_coursemodule_instance($event->contextinstanceid, $event->relateduserid);

            if ($objectid) {
                $grade = self::get_quiz_grade($event->objectid);

                block_ranking_manager::add_user_points($objectid, $grade);
            }

            return;
        }

        if (!self::is_completion_completed($event->objectid)) {
            return;
        }

        if (self::is_completion_repeated($event->courseid, $event->relateduserid, $event->contextinstanceid)) {
            return;
        }

        block_ranking_manager::add_user_points($event->objectid);
    }

    /**
     * Verify if the user is a student
     *
     * @param int $userid
     *
     * @return boolean
     */
    protected static function is_student($userid) {
        return user_has_role_assignment($userid, 5);
    }

    /**
     * Get the course completion instance
     *
     * @param int $coursemoduleid
     * @param int $userid
     *
     * @return mixed
     */
    protected static function get_coursemodule_instance($coursemoduleid, $userid) {
        global $DB;

        $cmc = $DB->get_record('course_modules_completion', array('coursemoduleid' => $coursemoduleid, 'userid' => $userid), '*');

        if ($cmc->id && $cmc->completionstate != 0) {
            return $cmc->id;
        }

        return false;
    }

    /**
     * Get the quiz attempt grade
     *
     * @param int $id
     *
     * @return mixed
     */
    protected static function get_quiz_grade($id) {
        global $DB;

        $grade = $DB->get_record('quiz_attempts', array('id' => $id), '*');

        return $grade->sumgrades;
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
                 count(*) as qtd
                FROM {ranking_points} p
                INNER JOIN {ranking_logs} l ON l.rankingid = p.id
                WHERE p.courseid = :courseid
                AND p.userid = :userid
                AND l.course_modules_completion = :cmcid";

        $params['courseid'] = $courseid;
        $params['userid'] = $userid;
        $params['cmcid'] = $cmcid;

        $qtd = $DB->get_record_sql($sql, $params);

        return (int) $qtd->qtd;
    }
}
