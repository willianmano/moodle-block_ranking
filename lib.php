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
 * Ranking block global lib
 *
 * @package    block_ranking
 * @copyright  2017 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define ('DEFAULT_POINTS', 2);

/**
 * Get the groups total points
 *
 * @return array
 */
function block_ranking_get_total_points_by_group() {
    global $COURSE, $DB;

    $sql = "SELECT
              g.name as groupname, SUM(rp.points) as points
            FROM {ranking_points} rp
            INNER JOIN {user} u ON u.id = rp.userid
            INNER JOIN {groups_members} gm ON gm.userid = rp.userid
            INNER JOIN {groups} g ON g.id = gm.groupid
            WHERE rp.courseid = :courseid
            GROUP BY g.name";

    $params['courseid'] = $COURSE->id;

    return array_values($DB->get_records_sql($sql, $params));
}

/**
 * Return the total of students of a group
 *
 * @return array
 */
function block_ranking_get_total_students_by_group() {
    global $COURSE, $DB;

    $sql = "SELECT
            g.name as groupname, count(g.name) as qtd
            FROM mdl_ranking_points rp
            INNER JOIN mdl_user u ON u.id = rp.userid
            INNER JOIN mdl_groups_members gm ON gm.userid = rp.userid
            INNER JOIN mdl_groups g ON g.id = gm.groupid
            WHERE rp.courseid = :courseid
            GROUP BY g.name";

    $params['courseid'] = $COURSE->id;

    return array_values($DB->get_records_sql($sql, $params));
}

/**
 * Get the groups total points
 *
 * @return mixed
 */
function block_ranking_get_average_points_by_group() {
    global $COURSE, $DB;

    $groups = block_ranking_get_total_points_by_group();

    $groupsmembersnumber = block_ranking_get_total_students_by_group();

    foreach ($groups as $key => $value) {
        foreach ($groupsmembersnumber as $group) {
            if ($value->groupname == $group->groupname) {
                $groups[$key]->points = $value->points / $group->qtd;
                continue 2;
            }
        }
    }

    return $groups;
}

/**
 * Return the group points evolution
 *
 * @param int $groupid
 * @return array
 */
function block_ranking_get_points_evolution_by_group($groupid) {
    global $DB;

    $sql = "SELECT
              rl.id, rl.points, rl.timecreated
            FROM {ranking_logs} rl
            INNER JOIN {ranking_points} rp ON rp.id = rl.rankingid
            INNER JOIN {groups_members} gm ON gm.userid = rp.userid
            INNER JOIN {groups} g ON g.id = gm.groupid
            WHERE g.id = :groupid AND rl.timecreated > :lastweek";

    $lastweek = time() - (7 * 24 * 60 * 60);

    $params['groupid'] = $groupid;
    $params['lastweek'] = $lastweek;

    return array_values($DB->get_records_sql($sql, $params));
}

/**
 * Return a graph of groups points evolution
 *
 * @param int $groupid
 * @return \core\chart_bar
 */
function block_ranking_create_group_points_evolution_chart($groupid) {
    $records = block_ranking_get_points_evolution_by_group($groupid);

    $pointsbydate = [];

    // Pega o primeiro registro, tira do array e soma os pontos na data.
    if (count($records)) {
        $firstrecord = array_shift($records);
        $lastdate = date('d-m-Y', $firstrecord->timecreated);

        $pointsbydate[$lastdate] = $firstrecord->points;
    }

    // Percorre os demais registros.
    if (count($records)) {
        foreach ($records as $points) {
            $currentdate = date('d-m-Y', $points->timecreated);

            if ($lastdate != $currentdate && !array_key_exists($currentdate, $pointsbydate)) {
                $lastdate = $currentdate;

                // Cria novo indice de novo data com valor zero.
                $pointsbydate[$lastdate] = 0;
            }

            $pointsbydate[$lastdate] += $points->points;
        }
    }

    if (empty($pointsbydate)) {
        return '';
    }

    $chart = new \core\chart_line(); // Create a bar chart instance.
    $chart->set_smooth(true);
    $series = new \core\chart_series(get_string('graph_group_evolution_title', 'block_ranking'), array_values($pointsbydate));
    $series->set_type(\core\chart_series::TYPE_LINE);
    $chart->add_series($series);
    $chart->set_labels(array_keys($pointsbydate));

    return $chart;
}

/**
 * Return a graph of groups points
 *
 * @return \core\chart_bar
 */
function block_ranking_create_groups_points_chart() {
    $groups = block_ranking_get_total_points_by_group();

    $labels = [];
    $values = [];
    foreach ($groups as $key => $value) {
        $labels[] = $value->groupname;
        $values[] = $value->points;
    }

    $chart = new \core\chart_bar(); // Create a bar chart instance.
    $series = new \core\chart_series(get_string('graph_groups', 'block_ranking'), $values);
    $chart->add_series($series);
    $chart->set_labels($labels);

    return $chart;
}

/**
 * Return a graph of groups points average
 *
 * @return \core\chart_bar
 */
function block_ranking_create_groups_points_average_chart() {
    $groups = block_ranking_get_average_points_by_group();

    $labels = [];
    $values = [];
    foreach ($groups as $key => $value) {
        $labels[] = $value->groupname;
        $values[] = $value->points;
    }

    $chart = new \core\chart_bar(); // Create a bar chart instance.
    $series = new \core\chart_series(get_string('graph_groups_avg', 'block_ranking'), $values);
    $chart->add_series($series);
    $chart->set_labels($labels);

    return $chart;
}
