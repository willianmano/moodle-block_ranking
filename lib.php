<?php

function block_ranking_get_students() {
	global $COURSE, $DB, $PAGE;

	$context = $PAGE->context;

	$userfields = user_picture::fields('u', array('username'));
	$sql = "SELECT DISTINCT $userfields, concat(u.firstname, ' ',u.lastname) as fullname
            FROM {user} u, {role_assignments} a
            WHERE a.contextid = :contextid
            AND a.userid = u.id
            AND a.roleid = :roleid
            LIMIT 10";
	$params['contextid'] = $context->id;
	$params['roleid'] = 5;

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
		      $content .= fatorial(6-$i);
		    $content .= HTML_WRITER::end_tag('td');
		    $content .= HTML_WRITER::end_tag('tr');
		}
	  $content .= HTML_WRITER::end_tag('tbody');
	$content .= HTML_WRITER::end_tag('table');

	return $content;
}
function fatorial($n) {
	if($n <= 0) {
		return 1;
	}
	return $n * fatorial($n-1);
}

// CRON FUNCTIONS
function block_rankging_get_modules_completion($lastid) {
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
			FROM mdl_course_modules_completion cmc
			INNER JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
			INNER JOIN mdl_modules m ON m.id = cm.module
			INNER JOIN mdl_course_completion_criteria ccc ON
				(ccc.course = cm.course AND ccc.module = m.name AND cm.id = ccc.moduleinstance)
			where cmc.id > 0 AND cmc.completionstate = 1
			ORDER BY cm.module";

	$params['id'] = $lastid;

	$completedModule = array_values($DB->get_records_sql($sql, $params));

	return $completedModule;
}