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
 * Ranking block settings file
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('block_ranking/rankingsize', get_string('rankingsize', 'block_ranking'),
        get_string('rankingsize_help', 'block_ranking'), 10, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking/resourcepoints', get_string('resourcepoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking/assignpoints', get_string('assignpoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking/forumpoints', get_string('forumpoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking/pagepoints', get_string('pagepoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking/workshoppoints', get_string('workshoppoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking/defaultpoints', get_string('defaultpoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configselect('block_ranking/enable_multiple_quizz_attempts', get_string('enable_multiple_quizz_attempts', 'block_ranking'),
        get_string('enable_multiple_quizz_attempts_help', 'block_ranking'), '1', array('1' => get_string('yes', 'block_ranking'), '0' => get_string('no', 'block_ranking'))));
}