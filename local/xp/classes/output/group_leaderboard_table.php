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
 * Leaderboard table.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use context_course;
use renderer_base;
use flexible_table;
use block_xp\local\leaderboard\leaderboard;
use block_xp\local\sql\limit;

/**
 * Leaderboard table.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_leaderboard_table extends flexible_table {

    /** @var leaderboard The leaderboard. */
    protected $leaderboard;
    /** @var block_xp_renderer XP Renderer. */
    protected $xpoutput = null;
    /** @var int[] The group IDs to highlight. */
    protected $groupids;

    /**
     * Constructor.
     *
     * @param leaderboard $leaderboard The leaderboard.
     * @param renderer_base $renderer The renderer.
     * @param int[] $groupid The current group.
     * @param array $options Options.
     */
    public function __construct(
            leaderboard $leaderboard,
            renderer_base $renderer,
            array $groupids,
            array $options = []
        ) {

        global $CFG, $USER;
        parent::__construct('block_xp_ladder');

        // The group IDs we're viewing the ladder for.
        $this->groupids = $groupids;

        // Block XP stuff.
        $this->leaderboard = $leaderboard;
        $this->xpoutput = $renderer;

        // Define columns, and headers.
        $columns = array_keys($this->leaderboard->get_columns());
        $headers = array_map(function($header) {
            return (string) $header;
        }, array_values($this->leaderboard->get_columns()));
        $this->define_columns($columns);
        $this->define_headers($headers);

        // Define various table settings.
        $this->sortable(false);
        $this->collapsible(false);
        $this->set_attribute('class', 'block_xp-table block_xp-group-ladder');
        $this->column_class('rank', 'col-rank');
        $this->column_class('grouppic', 'col-grouppic');
    }

    /**
     * Output the table.
     */
    public function out($pagesize) {
        $this->setup();

        $this->pagesize($pagesize, $this->leaderboard->get_count());
        $limit = new limit($pagesize, (int) $this->get_page_start());

        $ranking = $this->leaderboard->get_ranking($limit);
        foreach ($ranking as $rank) {
            $row = (object) [
                'rank' => $rank->get_rank(),
                'state' => $rank->get_state()
            ];
            $classes = (in_array($rank->get_state()->get_id(), $this->groupids)) ? 'highlight-row' : '';
            $this->add_data_keyed([
                'name' => $this->col_name($row),
                'rank' => $this->col_rank($row),
                'xp' => $this->col_xp($row)
            ], $classes);
        }
        $this->finish_output();
    }

    /**
     * Formats the column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_name($row) {
        $group = $row->state->get_group();
        $o = $this->col_grouppic($row);
        $o .= format_string($group->name, true, ['context' => context_course::instance($group->courseid)]);
        return $o;
    }

    /**
     * Formats the column.
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_rank($row) {
        return $row->rank;
    }

    /**
     * Formats the column.
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_xp($row) {
        return $this->xpoutput->xp($row->state->get_xp());
    }

    /**
     * Formats the column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_grouppic($row) {
        return $this->xpoutput->group_picture($row->state->get_group());
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        echo \html_writer::div(
            \block_xp\di::get('renderer')->notification_without_close(
                get_string('ladderempty', 'block_xp'),
                'info'
            ),
            '',
            ['style' => 'margin: 1em 0']
        );
    }
}
