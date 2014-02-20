<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once("$CFG->dirroot/blocks/ranking/lib.php");

    //--- general settings ---------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('block_ranking_resourcepoints', get_string('resourcepoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking_assignpoints', get_string('assignpoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking_forumpoints', get_string('forumpoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking_pagepoints', get_string('pagepoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking_workshoppoints', get_string('workshoppoints', 'block_ranking'),
        '', 2, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_ranking_defaultpoints', get_string('defaultpoints', 'block_ranking'),
        '', 2, PARAM_INT));
}

?>