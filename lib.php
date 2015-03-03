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

/**
 * Return the list of students in the course ranking
 *
 * @return array
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
                DISTINCT $userfields, concat(u.firstname, ' ',u.lastname) as fullname, r.points
            FROM
                {user} u
            INNER JOIN {role_assignments} a ON a.userid = u.id
            INNER JOIN {ranking_points} r ON r.userid = u.id
            INNER JOIN {context} c ON c.id = a.contextid
            WHERE a.contextid = :contextid
            AND a.userid = u.id
            AND a.roleid = :roleid
            AND c.instanceid = :courseid
            AND r.courseid = :crsid
            ORDER BY r.points DESC
            LIMIT " . $limit;
    $params['contextid'] = $context->id;
    $params['roleid'] = 5;
    $params['courseid'] = $COURSE->id;
    $params['crsid'] = $COURSE->id;

    $users = array_values($DB->get_records_sql($sql, $params));

    return $users;
}

/**
 * Build the ranking table to be viewd in the course
 * @param array $students List of students to be print in ranking block
 * @return string
 */
function block_ranking_print_students($students) {
    global $OUTPUT, $USER;

    $table = new html_table();
    $table->attributes = array("class" => "rankingTable table table-striped generaltable");
    $table->head = array(
                        get_string('table_position', 'block_ranking'),
                        get_string('table_name', 'block_ranking'),
                        get_string('table_points', 'block_ranking')
                    );
    for ($i = 0; $i < count($students); $i++) {
        $row = new html_table_row();

        // Verify if the logged user is one user in ranking.
        if ($students[$i]->id == $USER->id) {
            $row->attributes = array('class' => 'itsme');
        }
        $row->cells = array(
                        ($i + 1),
                        $OUTPUT->user_picture($students[$i], array('size' => 24, 'alttext' => false)) . ' '.$students[$i]->fullname,
                        $students[$i]->points
                    );
        $table->data[] = $row;
    }

    return html_writer::table($table);
}

// CRON FUNCTIONS.

/**
 * Mirror the course_modules_completion table into the ranking_cmc_mirror table
 *
 * @return bool
 */
function block_ranking_mirror_completions() {
    global $DB;

    $completedmodules = get_modules_completion_to_mirror();

    if (!empty($completedmodules)) {
        foreach ($completedmodules as $key => $module) {
            $DB->insert_record('ranking_cmc_mirror', $module);
        }
    }
}

/**
 * Returns course_modules_completion data to be mirrored into the ranking_cmc_mirror table
 *
 * @return array
 */
function get_modules_completion_to_mirror() {
    global $DB;

    $sql = "SELECT
                cmc.id as cmcid, cmc.coursemoduleid, cmc.userid, cmc.timemodified as timecreated, cmc.timemodified
            FROM
                {course_modules_completion} cmc
            WHERE
                cmc.id > (SELECT IFNULL(MAX(cmcid),0) FROM {ranking_cmc_mirror})
            ORDER BY cmc.id";

    $completedmodules = array_values($DB->get_records_sql($sql));

    return $completedmodules;
}

/**
 * Function executed by cron to calculate the student points
 *
 * @return bool
 */
function block_ranking_calculate_points() {
    global $DB;

    mtrace('ranking - entrei');

    $criteria = array(
        'plugin' => 'block_ranking',
        'name' => 'lastcomputedid'
    );

    $lastcomputedid = current($DB->get_records_sql('config_plugins', $criteria));

    $completedmodules = get_modules_completion($lastcomputedid->value);

    if (!empty($completedmodules)) {
        foreach ($completedmodules as $key => $usercompletion) {
            add_point_to_user($usercompletion);
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
 * Returns the modules completions
 *
 * @return array
 */
function get_modules_completion($lastcomputedid) {
    global $DB;

    $sql = "SELECT
                cmc.*,
                cmc.id as cmcid
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
                mdl_course_modules_completion cmc
            INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
            INNER JOIN mdl_modules m ON m.id = cm.module
            WHERE
                cmc.completionstate = 1
                AND cmc.id > :lastcomputedid
            ORDER BY cmc.id";

    $params['lastcomputedid'] = $lastcomputedid;

    $completedmodules = array_values($DB->get_records_sql($sql, $params));

    return $completedmodules;
}

/**
 * Add points to users
 *
 * @return bool
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
 * @return bool
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
    update_cmcm_completed($usercompletion->cmcmid);
}

/**
 * Save students points
 *
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
 * Mark completed completions as computed
 *
 * @return bool
 */
function update_cmcm_completed($cmcid) {
    global $DB;

    $data = new stdClass();
    $data->id = $cmcid;
    $data->computed = 1;
    $data->updated = time();

    $DB->update_record('ranking_cmc_mirror', $data);
}

// GET GRADES.

/**
 * Returns activity grade
 *
 * @return int
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
            $finalgrade = $gradeitem->finalgrade / 10;
        } else {
            $finalgrade = get_finalgrade_by_scale($gradeitem->finalgrade, $gradeitem->scaleid);
        }
    }

    return $finalgrade;
}

/**
 * Returns activity grade by scale
 *
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