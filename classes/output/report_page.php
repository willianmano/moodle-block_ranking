<?php
// This file is part of Moodle - http://moodle.org/
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
 * Block ranking report page
 *
 * @package    block_ranking
 * @copyright  2018 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_ranking\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use block_ranking\rankinglib;

/**
 * Report page renderable class.
 *
 * @package    block_ranking
 * @copyright  2018 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_page implements renderable, templatable {

    /** @var int the group. */
    protected $students;

    /** @var stdClass the course object. */
    protected $course;

    /** @var context_course the course context. */
    protected $context;

    /**
     * Constructor.
     *
     * @param int $courseid
     * @param int $contextid
     * @param int $perpage
     * @param int $groupid
     *
     * @return void
     */
    public function __construct($course, $context, $perpage = 100, $groupid = null) {
        $this->course = $course;

        $this->context = $context;

        $this->students = rankinglib::get_ranking_general_report($course->id, $context->id, $perpage, $groupid);
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $output = [
            'totalstudents' => count($this->students),
            'students' => rankinglib::prepare_ranking_data($this->students),
        ];

        if (has_capability('moodle/site:accessallgroups', $this->context)) {
            $groups = groups_get_all_groups($this->course->id);

            if (!empty($groups)) {
                groups_print_course_menu($this->course, $PAGE->url);
            }
        }

        return $output;
    }
}
