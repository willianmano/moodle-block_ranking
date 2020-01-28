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
 * Ranking block renderer.
 *
 * @package   block_ranking
 * @copyright 2018 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_ranking\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;

/**
 * Ranking block renderer.
 *
 * @package   block_ranking
 * @copyright 2020 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer the block instance in course to template.
     *
     * @param renderable $page
     *
     * @return bool|string
     *
     * @throws \moodle_exception
     */
    public function render_block(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('block_ranking/block', $data);
    }

    public function render_block_footer(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('block_ranking/block_footer', $data);
    }

    /**
     * Defer the general ranking in to template.
     *
     * @param renderable $page
     * @return bool|string
     * @throws \moodle_exception
     */
    public function render_report(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('block_ranking/report', $data);
    }
}
