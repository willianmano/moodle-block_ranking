<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Code run after the block_ranking block database tables have been created.
 * @return bool
 */
function xmldb_block_ranking_install() {
    global $DB;

    //basic block configuration
    $data = new stdClass();
    $data->id = 1;
    $data->name = 'lastid';
    $data->value = '0';

    $DB->insert_record('ranking', $data);

    return true;
}