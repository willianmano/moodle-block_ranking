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
 * Ranking block upgrade
 *
 * @package    contrib
 * @subpackage block_ranking
 * @copyright  2015 Willian Mano http://willianmano.net
 * @authors    Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the ranking block
 * @param int $oldversion
 * @param object $block
 * @return bool
 */

function xmldb_block_ranking_upgrade($oldversion, $block) {
    global $DB;

    if ($oldversion < 2015030300) {
        // Drop the mirror table.
        $dbman = $DB->get_manager();

        // Define table to be dropped.
        $table = new xmldb_table('ranking_cmc_mirror');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
    }

    if ($oldversion > 2015030300 && $oldversion < 2015051800) {
        $criteria = array(
            'plugin' => 'block_ranking',
            'name' => 'lastcomputedid'
        );

        $DB->delete_records('config_plugins', $criteria);
    }

    return true;
}