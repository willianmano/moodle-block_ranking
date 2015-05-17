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
 * Ranking block test class
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@link block_ranking}.
 * @group block_ranking_group
 */
class block_ranking_testcase extends advanced_testcase {
    public function test_adding() {
        $this->resetAfterTest(true);

        $this->assertEquals(2, 2);
    }
}