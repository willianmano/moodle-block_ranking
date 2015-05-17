<?php

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