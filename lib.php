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
 * @copyright  2014 Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define ('DEFAULT_POINTS', 2);

function block_ranking_get_students($limit = null) {
	global $COURSE, $DB, $PAGE, $CFG;

	//gest limit from default configuration or instance configuration
	if (!isset($limit) || !empty(trim($limit))) {
		if (isset($CFG->block_ranking_rankingsize) && !empty(trim($CFG->block_ranking_rankingsize))) {
			$limit = $CFG->block_ranking_rankingsize;
		} else {
			$limit = 10;
		}
	}

	$context = $PAGE->context;

	$userfields = user_picture::fields('u', array('username'));
	$sql = "SELECT DISTINCT $userfields, concat(u.firstname, ' ',u.lastname) as fullname, r.points
            FROM mdl_user u
            INNER JOIN mdl_role_assignments a ON a.userid = u.id
			INNER JOIN mdl_ranking_points r ON r.userid = u.id
			INNER JOIN mdl_context c ON c.id = a.contextid
            WHERE a.contextid = :contextid
            AND a.userid = u.id
            AND a.roleid = :roleid
            AND c.instanceid = :courseid
            ORDER BY r.points DESC
            LIMIT " . $limit;
	$params['contextid'] = $context->id;
	$params['roleid'] = 5;
	$params['courseid'] = $COURSE->id;

	$users = array_values($DB->get_records_sql($sql, $params));

	return $users;
}
function block_ranking_print_students($students) {
	global $OUTPUT, $USER;

	$tableoptions = array('class' => 'rankingTable table table-striped',
                          'cellpadding' => '0',
                          'cellspacing' => '0');

	$content  = HTML_WRITER::start_tag('table', $tableoptions);

	  $content .= HTML_WRITER::start_tag('thead');
	    $content .= HTML_WRITER::start_tag('tr');
	    $content .= HTML_WRITER::start_tag('td');
	      $content .= get_string('table_position', 'block_ranking');
	    $content .= HTML_WRITER::end_tag('td');
	    $content .= HTML_WRITER::start_tag('td');
	      $content .= get_string('table_name', 'block_ranking');
	    $content .= HTML_WRITER::end_tag('td');
	    $content .= HTML_WRITER::start_tag('td');
	      $content .= get_string('table_points', 'block_ranking');
	    $content .= HTML_WRITER::end_tag('td');
	    $content .= HTML_WRITER::end_tag('tr');
	  $content .= HTML_WRITER::end_tag('thead');

	  $content .= HTML_WRITER::start_tag('tbody');
		for ($i=0; $i < sizeof($students); $i++) {
			//verify if the logged user is one user in ranking
			$class = '';
			if($students[$i]->id == $USER->id) {
				$class = 'itsme';
			}

			$content .= HTML_WRITER::start_tag('tr', array('class'=> $class));
		    $content .= HTML_WRITER::start_tag('td');
		      $content .= ($i+1);
		    $content .= HTML_WRITER::end_tag('td');
		    $content .= HTML_WRITER::start_tag('td');
		      $content .= $OUTPUT->user_picture($students[$i], array('size'=>24, 'alttext'=>false)) . ' '.$students[$i]->fullname;
		    $content .= HTML_WRITER::end_tag('td');
		    $content .= HTML_WRITER::start_tag('td');
		      $content .= $students[$i]->points;
		    $content .= HTML_WRITER::end_tag('td');
		    $content .= HTML_WRITER::end_tag('tr');
		}
	  $content .= HTML_WRITER::end_tag('tbody');
	$content .= HTML_WRITER::end_tag('table');

	return $content;
}
// // // // // // 
// CRON FUNCTIONS
// // // // // // 
function block_ranking_mirror_completions() {
	global $DB;

	$completedModules = get_modules_completion_to_mirror();

	if(!empty($completedModules)) {
		$lastid = end($completedModules);
		//last id inserted
		$lastid = $lastid->cmcid;
	
		foreach ($completedModules as $key => $module) {
			$DB->insert_record('ranking_cmc_mirror', $module);
		}
	}
}
function get_modules_completion_to_mirror() {
	global $DB;

	$sql = "SELECT
				cmc.id as cmcid, cmc.coursemoduleid, cmc.userid, cmc.timemodified as timecreated, cmc.timemodified
			FROM
				{course_modules_completion} cmc
			WHERE
				cmc.id > (SELECT IFNULL(MAX(cmcid),0) FROM {ranking_cmc_mirror})
			ORDER BY cmc.id";

	$completedModules = array_values($DB->get_records_sql($sql));

	return $completedModules;
}
function block_ranking_calculate_points() {
	global $DB;

	$completedModules = get_modules_completion();

	if(!empty($completedModules)) {

		foreach ($completedModules as $key => $userCompletion) {
			add_point_to_user($userCompletion);
		}
		mtrace('... points computeds :P');
	} else {
		mtrace('... No new points to be computed');
	}
}
function get_modules_completion() {
	global $DB;

	$sql = "SELECT
				cmc.*,
				cm.course,
				cm.module as moduleid,
				cm.instance,
				cm.score,
				cm.indent,
				cm.completion,
				cm.completiongradeitemnumber,
				cm.completionview,
				cm.completionexpected,
				ccc.module,
				ccc.moduleinstance,
				m.name as modulename,
				cmcm.id as cmcmid
			FROM
				{course_modules_completion} cmc
			INNER JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
			INNER JOIN {modules} m ON m.id = cm.module
			INNER JOIN {course_completion_criteria} ccc ON
				(ccc.course = cm.course AND ccc.module = m.name AND cm.id = ccc.moduleinstance)
			INNER JOIN {ranking_cmc_mirror} cmcm ON cmcm.cmcid = cmc.id
			WHERE
				cmc.completionstate = 1
				AND cmcm.computed = 0
			ORDER BY cmc.id";

	$completedModules = array_values($DB->get_records_sql($sql));

	return $completedModules;
}

function add_point_to_user($userCompletion) {
	global $CFG;

	switch ($userCompletion->modulename) {
		case 'assign':
			add_default_points($userCompletion, $CFG->block_ranking_assignpoints);
		break;

		case 'resource':
			add_default_points($userCompletion, $CFG->block_ranking_resourcepoints);
		break;

		case 'forum':
			add_default_points($userCompletion, $CFG->block_ranking_forumpoints);
		break;

		case 'workshop':
			add_default_points($userCompletion, $CFG->block_ranking_workshoppoints);
		break;

		case 'page':
			add_default_points($userCompletion, $CFG->block_ranking_pagepoints);
		break;

		default:
			add_default_points($userCompletion, $CFG->block_ranking_defaultpoints);
		break;
	}
}

//Custom methods to add points to users depending of the activity
function add_default_points($userCompletion, $points) {
	if (!isset($points) || !empty(trim($points))) {
		$points = DEFAULT_POINTS;
	}
	if(!is_null($userCompletion->completiongradeitemnumber)) {
		$activityGrade = get_activity_finalgrade($userCompletion->modulename, $userCompletion->instance, $userCompletion->userid);
		$points += $activityGrade;
	}

	$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $points);
	add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $points);
	update_cmcm_completed($userCompletion->cmcmid);
}
function add_or_update_user_points($userid, $courseid, $points) {
	global $DB;

	$sql = "SELECT * FROM {ranking_points}
			WHERE userid = :userid AND courseid = :courseid";
	$params['userid'] = $userid;
	$params['courseid'] = $courseid;

	$userPoints = current($DB->get_records_sql($sql, $params));

	//user dont have points yet
	if(empty($userPoints)) {
		//basic block configuration
	    $userPoints = new stdClass();
	    $userPoints->userid = $userid;
	    $userPoints->courseid = $courseid;
	    $userPoints->points = $points;
	    $userPoints->timecreated = time();
	    $userPoints->timemodified = time();

	    $rankingid = $DB->insert_record('ranking_points', $userPoints, true);
	} else {
		$userPoints->points = $userPoints->points + $points;
		$userPoints->timemodified = time();
		$DB->update_record('ranking_points', $userPoints);
		$rankingid = $userPoints->id;
	}
	return $rankingid;
}
function add_ranking_log($rankingid, $courseid, $cmc, $points) {
	global $DB;

	$rankingLog = new stdClass();
	$rankingLog->rankingid = $rankingid;
	$rankingLog->courseid = $courseid;
	$rankingLog->course_modules_completion = $cmc;
	$rankingLog->points = $points;
	$rankingLog->timecreated = time();

	$logid = $DB->insert_record('ranking_logs', $rankingLog, true);

	return $logid;
}
function update_cmcm_completed($cmcid) {
	global $DB;

	$data = new stdClass();
	$data->id = $cmcid;
	$data->computed = 1;
	$data->updated = time();

	$DB->update_record('ranking_cmc_mirror', $data);
}
// GET GRADES
function get_activity_finalgrade($activity, $activityid, $userid) {
	global $DB;
	$sql = "SELECT
				gg.itemid, gg.userid, gg.rawscaleid, gg.finalgrade, gi.scaleid
				FROM mdl_grade_grades gg
			INNER JOIN mdl_grade_items gi ON gi.id = gg.itemid
			WHERE gi.itemmodule = :activity AND gi.iteminstance = :iteminstance AND gg.userid = :userid";
	$params['activity'] = $activity;
	$params['iteminstance'] = $activityid;
	$params['userid'] = $userid;

	$gradeItem = $DB->get_records_sql($sql, $params);

	$finalgrade = 0;
	if( !empty($gradeItem)) {
		$gradeItem = current($gradeItem);

		//grade without scale -- grademax 100
		if( empty($gradeItem->scaleid)) {
			$finalgrade = $gradeItem->finalgrade / 10;
		} else {
			$finalgrade = get_finalgrade_by_scale($gradeItem->finalgrade, $gradeItem->scaleid);
		}
	}

	return $finalgrade;
}
function get_finalgrade_by_scale($finalgrade, $scaleid) {
	global $DB;

	$sql = "SELECT scale FROM mdl_scale WHERE id = :scaleid";
	$params['scaleid'] = $scaleid;

	$scale = $DB->get_records_sql($sql, $params);

	if(!empty($scale)) {
		$scale = current($scale);
		$scale = explode(',', $scale->scale);

		//fix zero based problem
		$finalgrade = $scale[$finalgrade - 1];
	}

	return $finalgrade;
}