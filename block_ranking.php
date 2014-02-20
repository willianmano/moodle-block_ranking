<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/ranking/lib.php');

class block_ranking extends block_base {
	public function init() {
		$this->title = get_string('ranking', 'block_ranking');
	}
	public function get_content() {

		$this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

		$users = block_ranking_get_students();

		if(empty($users)) {
			$this->content->text = get_string('nostudents', 'block_ranking');
		} else {
			$this->content->text = block_ranking_print_students($users);
		}

        return $this->content;
	}
	public function cron() {

	    block_ranking_calculate_points();

        return true;
    }
    function has_config() {
    	return true;
    }
}