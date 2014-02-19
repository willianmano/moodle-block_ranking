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
function block_ranking_print_students($users) {
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
		for ($i=0; $i < sizeof($users); $i++) { 
			$content .= HTML_WRITER::start_tag('tr');
		    $content .= HTML_WRITER::start_tag('td');
		      $content .= ($i+1);
		    $content .= HTML_WRITER::end_tag('td');
		    $content .= HTML_WRITER::start_tag('td');
		      $content .= $OUTPUT->user_picture($users[$i], array('size'=>24, 'alttext'=>false)) . ' '.$users[$i]->fullname;
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