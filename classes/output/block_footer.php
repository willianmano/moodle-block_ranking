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

use renderable;
use templatable;
use renderer_base;

/**
 * Autoglass reports block renderable class.
 *
 * @package    block_ranking
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_footer implements renderable, templatable {

    /**
     * @var int $courseid The ranking course id.
     */
    protected $courseid;

    /**
     * Block constructor.
     * @param int $courseid
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $buttons[] = [
            'title' => get_string('see_full_ranking', 'block_ranking'),
            'url' => new \moodle_url('/blocks/ranking/report.php', ['courseid' => $this->courseid])
        ];

        $context = \context_course::instance($this->courseid);
        if (has_capability('moodle/site:accessallgroups', $context)) {
            $buttons[] = [
                'title' => get_string('ranking_graphs', 'block_ranking'),
                'url' => new \moodle_url('/blocks/ranking/graphs.php', ['courseid' => $this->courseid])
            ];
        }

        return ['footerbuttons' => $buttons];
    }
}
