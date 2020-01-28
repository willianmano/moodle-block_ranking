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
 * Ranking block
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_ranking\output;

defined('MOODLE_INTERNAL') || die();

use block_ranking\rankinglib;
use block_ranking\studentlib;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking block renderable class.
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block implements renderable, templatable {

    /**
     * @var int $rankingsize the ranking size.
     */
    protected $rankingsize;

    /**
     * Block constructor.
     * @param int $rankingsize
     */
    public function __construct($rankingsize) {
        $this->rankingsize = $rankingsize;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \coding_exception
     *
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output) {
        $rankinglib = new rankinglib();

        $weekstart = strtotime(date('d-m-Y', strtotime('-'.date('w').' days')));
        $monthstart = strtotime(date('Y-m-01'));

        $returndata = [
            'generalranking' => $rankinglib->get_students($this->rankingsize),
            'weeklyranking' => $rankinglib->get_students_by_date($weekstart, time(), $this->rankingsize),
            'monthlyranking' => $rankinglib->get_students_by_date($monthstart, time(), $this->rankingsize)
        ];

        $studentlib = new studentlib();
        if ($studentlib->is_student()) {
            $returndata['studentdata'] = [
                'generalpoints' => $studentlib->get_total_course_points(),
                'weeklypoints' => $studentlib->get_student_points_by_date($weekstart, time()),
                'monthlypoints' => $studentlib->get_student_points_by_date($monthstart, time())
            ];
        }

        return $returndata;
    }
}
