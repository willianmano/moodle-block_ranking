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
 * Ranking block - report page
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/blocks/ranking/lib.php');

define('DEFAULT_PAGE_SIZE', 100);

$courseid = required_param('courseid', PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$group = optional_param('group', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($courseid);
$context = context_course::instance($courseid);

// Some stuff.
$url = new moodle_url('/blocks/ranking/report.php', array('courseid' => $courseid));
if ($action) {
    $url->param('action', $action);
}

// Page info.
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title($course->fullname.': Ranking geral dos alunos');
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_url($url);

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
$params['courseid'] = $COURSE->id;
$params['r_courseid'] = $params['courseid'];

$order = "ORDER BY r.points DESC, u.firstname ASC
        LIMIT " . $perpage;

if ($group) {
    $from .= " INNER JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :groupid";
    $params['groupid'] = $group;
}

$sql = "SELECT $userfields, r.points $from $where $order";

$students = array_values($DB->get_records_sql($sql, $params));

$strcoursereport = get_string('nostudents', 'block_ranking');;
if (count($students)) {
    $strcoursereport = get_string('report_head', 'block_ranking', count($students));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strcoursereport);
$PAGE->set_title($strcoursereport);

// Output group selector if there are groups in the course.
echo $OUTPUT->container_start('ranking-report');

if (has_capability('moodle/site:accessallgroups', $context)) {
    $groups = groups_get_all_groups($course->id);
    if (!empty($groups)) {
        groups_print_course_menu($course, $PAGE->url);
    }
}

echo generate_table($students);

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
