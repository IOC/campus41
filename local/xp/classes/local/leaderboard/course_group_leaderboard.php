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
 * Course group leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\leaderboard;
defined('MOODLE_INTERNAL') || die();

use lang_string;
use moodle_database;
use stdClass;
use block_xp\local\sql\limit;
use block_xp\local\xp\state_rank;
use local_xp\local\xp\levelless_group_state;

/**
 * Course group leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_group_leaderboard implements \block_xp\local\leaderboard\leaderboard {

    /** @var moodle_database The database. */
    protected $db;
    /** @var int The course ID. */
    protected $courseid;
    /** @var string The DB table. */
    protected $table = 'block_xp';
    /** @var array The group static cache. */
    protected $groupcache;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param int $courseid The course ID.
     */
    public function __construct(moodle_database $db, $courseid) {
        $this->db = $db;
        $this->courseid = $courseid;
        $this->fields = 'g.id, SUM(x.xp) AS xp';
        $this->from = "{{$this->table}} x
                  JOIN {groups} g
                    ON g.courseid = x.courseid
                  JOIN {groups_members} gm
                    ON gm.userid = x.userid
                   AND gm.groupid = g.id";
        $this->where = "x.courseid = :courseid";
        $this->groupby = "g.id";
        $this->order = "SUM(x.xp) DESC, g.id ASC";
        $this->params = [
            'courseid' => $this->courseid,
        ];
    }

    /**
     * Get the leaderboard columns.
     *
     * @return array Where keys are column identifiers and values are lang_string objects.
     */
    public function get_columns() {
        return [
            'rank' => new lang_string('rank', 'block_xp'),
            'name' => new lang_string('groupname', 'group'),
            'xp' => new lang_string('total', 'block_xp'),
        ];
    }

    /**
     * Get the number of rows in the leaderboard.
     *
     * @return int
     */
    public function get_count() {
        $sql = "SELECT COUNT('x')
                  FROM {groups} g2
                 WHERE g2.id IN (
                       SELECT g.id
                         FROM {$this->from}
                        WHERE {$this->where}
                    )";
        return $this->db->count_records_sql($sql, $this->params);
    }

    /**
     * Get a group.
     *
     * @param int $id The ID.
     * @return stdClass
     */
    protected function get_group($id) {
        if ($this->groupcache === null) {
            $this->groupcache = groups_get_all_groups($this->courseid);
        }
        return $this->groupcache[$id];
    }

    /**
     * Get the points of an object.
     *
     * @param int $id The object ID.
     * @return int|false False when not ranked.
     */
    protected function get_points($id) {
        $sql = "SELECT SUM(x.xp) AS xp
                  FROM {$this->from}
                 WHERE {$this->where}
                   AND (g.id = :groupid)
              GROUP BY {$this->groupby}";
        $params = $this->params + ['groupid' => $id];
        return $this->db->get_field_sql($sql, $params);
    }

    /**
     * Return the position of the object.
     *
     * The position is used to determine how to paginate the leaderboard.
     *
     * @param int $id The object ID.
     * @return int Indexed from 0, null when not ranked.
     */
    public function get_position($id) {
        $xp = $this->get_points($id);
        return $xp === false ? null : $this->get_position_with_xp($id, $xp);
    }

    /**
     * Get position based on ID and XP.
     *
     * @param int $id The object ID..
     * @param int $xp The amount of XP.
     * @return int Indexed from 0.
     */
    protected function get_position_with_xp($id, $xp) {
        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT g.id
                      FROM {$this->from}
                     WHERE {$this->where}
                  GROUP BY {$this->groupby}
                    HAVING (SUM(x.xp) > :posxp
                        OR (SUM(x.xp) = :posxpeq AND g.id < :posid))
                       ) countx ";
        $params = $this->params + [
            'posxp' => $xp,
            'posxpeq' => $xp,
            'posid' => $id
        ];
        return $this->db->count_records_sql($sql, $params);
    }

    /**
     * Get the rank of an object.
     *
     * @param int $id The object ID.
     * @return rank|null
     */
    public function get_rank($id) {
        $state = $this->get_state($id);
        if (!$state) {
            return null;
        }
        $rank = $this->get_rank_from_xp($state->get_xp());
        return new state_rank($rank, $state);
    }

    /**
     * Get the rank of an amount of XP.
     *
     * @param int $xp The xp.
     * @return int Indexed from 1.
     */
    protected function get_rank_from_xp($xp) {
        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT g.id
                      FROM {$this->from}
                     WHERE {$this->where}
                  GROUP BY {$this->groupby}
                    HAVING (SUM(x.xp) > :posxp)
                  ) countx";
        return $this->db->count_records_sql($sql, $this->params + ['posxp' => $xp]) + 1;
    }

    /**
     * Get the ranking.
     *
     * @param limit $limit The limit.
     * @return Traversable
     */
    public function get_ranking(limit $limit) {
        $recordset = $this->get_ranking_recordset($limit);

        $rank = null;
        $offset = null;
        $lastxp = null;
        $ranking = [];

        foreach ($recordset as $record) {
            $state = $this->make_state_from_record($record);

            if ($rank === null || $lastxp !== $state->get_xp()) {
                if ($rank === null) {
                    $pos = $this->get_position_with_xp($state->get_id(), $state->get_xp());
                    $rank = $this->get_rank_from_xp($state->get_xp());
                    $offset = 1 + ($pos + 1 - $rank);
                } else {
                    $rank += $offset;
                    $offset = 1;
                }
                $lastxp = $state->get_xp();
            } else {
                $offset++;
            }

            $ranking[] = new state_rank($rank, $state);
        }

        $recordset->close();
        return $ranking;
    }

    /**
     * Get ranking recordset.
     *
     * @param limit $limit The limit.
     * @return moodle_recordset
     */
    protected function get_ranking_recordset(limit $limit) {
        $sql = "SELECT {$this->fields}
                  FROM {$this->from}
                 WHERE {$this->where}
              GROUP BY {$this->groupby}
              ORDER BY {$this->order}";
        if ($limit) {
            $recordset = $this->db->get_recordset_sql($sql, $this->params, $limit->get_offset(), $limit->get_count());
        } else {
            $recordset = $this->db->get_recordset_sql($sql, $this->params);
        }
        return $recordset;
    }

    /**
     * Get the state.
     *
     * @param int $id The object ID.
     * @return state|null
     */
    protected function get_state($id) {
        $sql = "SELECT {$this->fields}
                  FROM {$this->from}
                 WHERE {$this->where}
                   AND (g.id = :groupid)
              GROUP BY {$this->groupby}";
        $params = $this->params + ['groupid' => $id];
        $record = $this->db->get_record_sql($sql, $params);
        return !$record ? null : $this->make_state_from_record($record);
    }

    /**
     * Make a state from the record.
     *
     * @param stdClass $record The row.
     * @return state
     */
    protected function make_state_from_record(stdClass $record) {
        $xp = !empty($record->xp) ? $record->xp : 0;
        return new levelless_group_state($this->get_group($record->id), $xp);
    }
}
