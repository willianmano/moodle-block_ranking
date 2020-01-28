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
 * @package    block_ranking
 * @copyright  2017 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

define('DEFAULT_PAGE_SIZE', 100);

$courseid = required_param('courseid', PARAM_INT);
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$group = optional_param('group', null, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($courseid);
$context = context_course::instance($courseid);

$params = ['courseid' => $courseid];

if ($perpage) {
    $params['perpage'] = $perpage;
}

if ($group) {
    $params['group'] = $group;
}

$url = new moodle_url('/blocks/ranking/report.php', $params);

// Page info.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$title = get_string('report_title', 'block_ranking', $course->fullname);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$PAGE->navbar->add(get_string('pluginname', 'block_ranking'));

$output = $PAGE->get_renderer('block_ranking');

echo $output->header();
echo $output->container_start('ranking-report');

if (has_capability('moodle/course:managegroups', $context)) {
    $groups = groups_get_all_groups($course->id);
    if (!empty($groups)) {
        groups_print_course_menu($course, $PAGE->url);
    }
}

$renderable = new \block_ranking\output\report($perpage, $group);

echo $output->render($renderable);

echo $output->container_end();

echo $output->footer();
