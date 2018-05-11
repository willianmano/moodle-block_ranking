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
 * Zegna ranking block - block renderer
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
 * Block renderable class.
 *
 * @package    block_ranking
 * @copyright  2018 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block implements renderable, templatable {

    /** @var stdClass course. */
    protected $course;

    /** @var context_coure context. */
    protected $context;

    /** @var stdClass last week ranking. */
    protected $rankinglastweek;

    /** @var stdClass last month ranking. */
    protected $rankinglastmonth;

    /** @var stdClass general ranking. */
    protected $rankinggeral;

    /**
     * Constructor.
     *
     * @param stdClass $course
     * @param context_course $context
     * @param int $rankingsize
     *
     * @return void
     */
    public function __construct($course, $context, $rankingsize = null) {
        $this->course = $course;
        $this->context = $context;

        $cfgranking = get_config('block_ranking');

        // Get rankingsize from default configuration or instance configuration.
        if (!$rankingsize && isset($cfgranking->rankingsize) && trim($cfgranking->rankingsize) != '') {
            $rankingsize = $cfgranking->rankingsize;
        }

        $weekstart = strtotime(date('d-m-Y', strtotime('-'.date('w').' days')));
        $monthstart = strtotime(date('Y-m-01'));

        $this->rankinglastweek = rankinglib::get_ranking_by_date($course, $context, $weekstart, time(), $rankingsize);
        $this->rankinglastmonth = rankinglib::get_ranking_by_date($course, $context, $monthstart, time(), $rankingsize);
        $this->rankinggeral = rankinglib::get_ranking_general($course, $context, $rankingsize);
        $this->rankingindividual = rankinglib::get_ranking_individual($course->id, $weekstart, $monthstart);
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     *
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        return [
            'rankinglastweek' => $this->prepare_ranking_data($this->rankinglastweek),
            'rankinglastmonth' => $this->prepare_ranking_data($this->rankinglastmonth),
            'rankinggeral' => $this->prepare_ranking_data($this->rankinggeral),
            'rankingindividual' => $this->rankingindividual
        ];
    }

    protected function prepare_ranking_data($data = null) {
        global $USER, $OUTPUT;

        if (!$data) {
            return null;
        }

        $lastpos = 1;
        $lastpoints = current($data)->points;
        $returndata = [];
        for ($i = 0; $i < count($data); $i++) {
            // Verify if the logged user is present in ranking.
            $class = '';
            if ($data[$i]->id == $USER->id) {
                $class = 'itsme';
            }

            if ($lastpoints > $data[$i]->points) {
                $lastpos++;
                $lastpoints = $data[$i]->points;
            }

            $returndata[$i] = [
                'pos' => $lastpos,
                'user' => $OUTPUT->user_picture($data[$i], array('size' => 24, 'alttext' => false)) . ' '.$data[$i]->firstname,
                'points' => $data[$i]->points ?: '-',
                'class' => $class
            ];
        }

        return $returndata;
    }
}
