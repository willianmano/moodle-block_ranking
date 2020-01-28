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
 * Ranking block report page
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_ranking\output;

defined('MOODLE_INTERNAL') || die();

use block_ranking\rankinglib;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking block report renderable class.
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report implements renderable, templatable {

    /**
     * @var int $rankingsize The ranking size.
     */
    protected $rankingsize;

    /**
     * @var int $group The moodle group.
     */
    protected $group;

    /**
     * Block constructor.
     *
     * @param int $rankingsize
     * @param int $group
     */
    public function __construct($rankingsize = 100, $group = null) {
        $this->rankingsize = $rankingsize;
        $this->group = $group;
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

        $students = $rankinglib->get_students($this->rankingsize, $this->group);

        return [
            'students' => $students
        ];
    }
}
