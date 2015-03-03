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
 * Ranking block configuration form definition
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class block_ranking_edit_form extends block_edit_form {

    public function specific_definition($mform) {
        global $CFG;

        $mform->addElement('header', 'displayinfo', get_string('configuration', 'block_ranking'));

        $mform->addElement('text', 'config_ranking_title', get_string('blocktitle', 'block_ranking'));
        $mform->setDefault('config_ranking_title', get_string('ranking', 'block_ranking'));
        $mform->addRule('config_ranking_title', null, 'required', null, 'client');

        $mform->addElement('text', 'config_ranking_rankingsize', get_string('rankingsize', 'block_ranking'));
        $mform->setDefault('config_ranking_rankingsize', $CFG->block_ranking_rankingsize);
        $mform->setType('config_ranking_rankingsize', PARAM_INT);
    }
}