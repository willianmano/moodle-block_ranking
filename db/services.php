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
 *
 * Ranking external functions and service definitions.
 *
 * @package    block_ranking
 * @copyright  2016 J. Kalkhof <jerry@ccadapps.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.1
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'blocks_ranking_get_ranking' => array(
        'classname' => 'blocks_ranking_external',
        'classpath'   => 'blocks/ranking/classes/external.php',
        'methodname' => 'get_ranking',
        'description' => 'Returns a list of students and rank.',
        'type' => 'read',
        'capabilities' => 'mod/data:viewentry',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);
