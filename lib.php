<?php

function block_ranking_get_students() {
	global $COURSE, $DB, $PAGE;

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
            LIMIT 10";
	$params['contextid'] = $context->id;
	$params['roleid'] = 5;
	$params['courseid'] = $COURSE->id;

	$users = array_values($DB->get_records_sql($sql, $params));

	return $users;
}
function block_ranking_print_students($students) {
	global $OUTPUT;
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
			$content .= HTML_WRITER::start_tag('tr');
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

// CRON FUNCTIONS
function block_ranking_calculate_points() {
	global $DB;

	$completedModules = get_modules_completion();

	if(!empty($completedModules)) {
		$lastid = end($completedModules);
		//last id inserted
		$lastid = $lastid->id;
		if($lastid) {
			//update the lastid on ranking configuration
			update_ranking_lastid($lastid);
		}

		foreach ($completedModules as $key => $userCompletion) {
			add_point_to_user($userCompletion);
		}
	}
	mtrace('... No new points to be computed');
}

function update_ranking_lastid($lastid) {
	global $DB;

	$sql = "SELECT id, name, value FROM {ranking} WHERE name = 'lastid'";

	$ranking = current($DB->get_records_sql($sql));
	$ranking->value = $lastid;

	$DB->update_record('ranking', $ranking);
}

function get_modules_completion() {
	global $DB;

	$sql = "SELECT
				cmc.*,
				cm.course,
				cm.module,
				cm.instance,
				cm.score,
				cm.indent,
				cm.completion,
				cm.completiongradeitemnumber,
				cm.completionview,
				cm.completionexpected,
				ccc.module,
				ccc.moduleinstance,
				m.name as modulename
			FROM {course_modules_completion} cmc
			INNER JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
			INNER JOIN {modules} m ON m.id = cm.module
			INNER JOIN {course_completion_criteria} ccc ON
				(ccc.course = cm.course AND ccc.module = m.name AND cm.id = ccc.moduleinstance)
			WHERE
				cmc.id > (SELECT value FROM {ranking} WHERE name = 'lastid')
				AND cmc.completionstate = 1
			ORDER BY cmc.id";

	$completedModules = array_values($DB->get_records_sql($sql));

	return $completedModules;
}

function add_point_to_user($userCompletion) {
	global $CFG;

	switch ($userCompletion->modulename) {
		case 'assign':
				$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $CFG->block_ranking_assignpoints);
				add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $CFG->block_ranking_assignpoints);
		break;

		case 'resource':
				$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $CFG->block_ranking_resourcepoints);
				add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $CFG->block_ranking_resourcepoints);
		break;

		case 'forum':
				$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $CFG->block_ranking_forumpoints);
				add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $CFG->block_ranking_forumpoints);
		break;

		case 'workshop':
				$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $CFG->block_ranking_workshoppoints);
				add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $CFG->block_ranking_workshoppoints);
		break;

		case 'page':
				$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $CFG->block_ranking_pagepoints);
				add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $CFG->block_ranking_pagepoints);
		break;

		default:
				$rankingid = add_or_update_user_points($userCompletion->userid, $userCompletion->course, $CFG->block_ranking_defaultpoints);
				add_ranking_log($rankingid, $userCompletion->course, $userCompletion->id, $CFG->block_ranking_defaultpoints);
		break;
	}
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