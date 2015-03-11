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

$courseid = required_param('courseid', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);
$resetdata = optional_param('resetdata', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);

require_login($courseid);
$context = context_course::instance($courseid);

// We need to be able to add this block to edit the course properties.
require_capability('block/ranking:addinstance', $context);

// Some stuff.
$url = new moodle_url('/blocks/ranking/report.php', array('courseid' => $courseid));
if ($action) {
    $url->param('action', $action);
}
$strcoursereport = 'Relatório do curso';

// Page info.
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title($strcoursereport);
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_url($url);

echo $OUTPUT->header();
echo $OUTPUT->heading($strcoursereport);

// Displaying the report.

echo $OUTPUT->footer();
