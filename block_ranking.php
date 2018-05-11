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
     * @return string
     */
    public function get_content() {
        $rankingsize = isset($this->config->ranking_rankingsize) ? trim($this->config->ranking_rankingsize) : 0;

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $renderable = new \block_ranking\output\block($this->page->course, $this->page->context, $rankingsize);

        $renderer = $this->page->get_renderer('block_ranking');

        $this->content->text = $renderer->render($renderable);

        $this->content->footer = html_writer::tag('p',
                                html_writer::link(
                                    new moodle_url(
                                        '/blocks/ranking/report.php',
                                        array('courseid' => $this->page->course->id)
                                    ),
                                    get_string('see_full_ranking', 'block_ranking'),
                                    array('class' => 'btn btn-default')
                                )
                          );

        if (has_capability('moodle/site:accessallgroups', $context = $this->page->context)) {
            $this->content->footer .= html_writer::tag('p',
                                          html_writer::link(
                                              new moodle_url(
                                                  '/blocks/ranking/graphs.php',
                                                  array('courseid' => $this->page->course->id)
                                              ),
                                              get_string('ranking_graphs', 'block_ranking'),
                                              array('class' => 'btn btn-default')
                                          )
                                    );
        }

        $this->content->footer .= "
            <script type='text/javascript'>
                Y.use('tabview', function(Y) {
                    var tabview = new Y.TabView({srcNode: '#ranking-tabs'});
                    tabview.render();
                });
            </script>";

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
