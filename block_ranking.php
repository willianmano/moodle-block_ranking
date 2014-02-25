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
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2014 Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/ranking/lib.php');

class block_ranking extends block_base {

    /**
     * Sets the block title
     *
     * @return void
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
        if (isset($this->config->ranking_title) && !empty(trim($this->config->ranking_title))) {
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
     * @return string
     */
    public function get_content() {
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $users = block_ranking_get_students($this->config->ranking_rankingsize);

        if (empty($users)) {
            $this->content->text = get_string('nostudents', 'block_ranking');
        } else {
            $this->content->text = block_ranking_print_students($users);
        }

        return $this->content;
    }

    /**
     * Executes the cron job
     *
     * @return bool
     */
    public function cron() {

        block_ranking_mirror_completions();;

        block_ranking_calculate_points();

        return true;
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