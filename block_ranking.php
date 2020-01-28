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
 * Ranking block definition
 *
 * @package   block_ranking
 * @copyright 2017 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/ranking/lib.php');

/**
 * Ranking block definition class
 *
 * @copyright 2017 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ranking extends block_base {

    /**
     * Sets the block title
     *
     * @return void
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('ranking', 'block_ranking');
    }

    /**
     * Controls the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        $title = isset($this->config->ranking_title) ? trim($this->config->ranking_title) : '';
        if (!empty($title)) {
            $this->title = format_string($this->config->ranking_title);
        }
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => false,
            'mod'            => false,
            'my'             => false
        );
    }

    /**
     * Creates the blocks main content
     *
     * @return stdClass|stdObject
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_content() {
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $rankingsize = isset($this->config->ranking_rankingsize) ? trim($this->config->ranking_rankingsize) : null;
        if (!$rankingsize) {
            $rankingsize = 10;

            $cfgranking = get_config('block_ranking');

            if (isset($cfgranking->rankingsize) && trim($cfgranking->rankingsize) != '') {
                $rankingsize = $cfgranking->rankingsize;
            }
        }

        $renderer = $this->page->get_renderer('block_ranking');

        $contentrenderable = new \block_ranking\output\block($rankingsize);
        $this->content->text = $renderer->render($contentrenderable);

        $footerrenderable = new \block_ranking\output\block_footer($this->page->course->id);
        $this->content->footer = $renderer->render($footerrenderable);

        return $this->content;
    }

    /**
     * Allow block instance configuration
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
}
