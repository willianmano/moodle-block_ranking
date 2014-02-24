<?php


defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class block_ranking_edit_form extends block_edit_form {

	public function specific_definition($mform) {
		global $CFG;

		// add group for text areas
		$mform->addElement('header','displayinfo', get_string('configuration', 'block_ranking'));
		 
		$mform->addElement('text', 'config_ranking_title', get_string('blocktitle', 'block_ranking'));
		$mform->setDefault('config_ranking_title', get_string('ranking', 'block_ranking'));
		$mform->addRule('config_ranking_title', null, 'required', null, 'client');

		$mform->addElement('text', 'config_ranking_rankingsize', get_string('rankingsize', 'block_ranking'));
		$mform->setDefault('config_ranking_rankingsize', $CFG->block_ranking_rankingsize);
		$mform->setType('config_ranking_rankingsize', PARAM_INT);
	}
}