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
 * Ranking block - graphs page
 *
 * @package    block_ranking
 * @copyright  2017 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/blocks/ranking/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$reporttype = optional_param('reporttype', null, PARAM_ALPHA);
$group = optional_param('group', null, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($courseid);

$context = context_course::instance($courseid);

if (!has_capability('moodle/site:accessallgroups', $context)) {
    redirect(new moodle_url('/course/view.php',
                            ['id' => $courseid]),
                            get_string('graph_access_deny', 'block_ranking'));
}

$groups = groups_get_all_groups($course->id);
if (empty($groups)) {
    redirect(new moodle_url('/course/view.php',
                           ['id' => $courseid]),
                           get_string('graph_no_groups', 'block_ranking'));
}

// Some stuff.
$url = new moodle_url('/blocks/ranking/graphs.php', array('courseid' => $courseid));

if ($reporttype == 'groupevolution') {
    $url->param('reporttype', $reporttype);
}

// Page info.
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title($course->fullname . ': ' . get_string('ranking_graphs', 'block_ranking'));
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_url($url);

$strcoursereport = get_string('ranking_graphs', 'block_ranking');
echo $OUTPUT->header();
echo $OUTPUT->heading($strcoursereport);
$PAGE->set_title($strcoursereport);

// Output group selector if there are groups in the course.
echo $OUTPUT->container_start('ranking-graphs');

$types = [
    "group" => get_string('graph_groups', 'block_ranking'),
    "groupavg" => get_string('graph_groups_avg', 'block_ranking'),
    "groupevolution" => get_string('graph_group_evolution', 'block_ranking')
];

$select = new single_select(new moodle_url($url), 'reporttype', $types, $reporttype, null, 'selectgroup');
$select->label = get_string('graph_types', 'block_ranking');
echo $OUTPUT->render($select);

$chart = '';
if ($reporttype == '' || $reporttype == 'group') {
    $chart = block_ranking_create_groups_points_chart();
}

if ($reporttype == 'groupavg') {
    $chart = block_ranking_create_groups_points_average_chart();
}

if ($reporttype == 'groupevolution') {
    $groups = groups_get_all_groups($course->id);
    if (!empty($groups)) {
        groups_print_course_menu($course, $PAGE->url);
    }
}

if ($reporttype == 'groupevolution' && $group != '') {
    $chart = block_ranking_create_group_points_evolution_chart($group);
}

if ($chart == '' && $reporttype == 'groupevolution') {
    echo "<h3>".get_string('graph_select_a_group', 'block_ranking')."</h3>";
}

if ($chart != '') {
    echo $OUTPUT->render($chart);
}

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
