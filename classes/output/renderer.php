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
 * Zegna ranking block renderer.
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
 * Zegna ranking block renderer.
 *
 * @package   block_ranking
 * @copyright 2017 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer the block instance in course to template.
     *
     * @param renderable $page
     *
     * @return string
     */
    public function render_block(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('block_ranking/block', $data);
    }

    /**
     * Defer the report page to template.
     *
     * @param renderable $page
     *
     * @return string
     */
    public function render_report_page(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('block_ranking/report_page', $data);
    }
}
