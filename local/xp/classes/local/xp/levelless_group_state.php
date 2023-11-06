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
 * Level-less group state.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use renderable;
use stdClass;
use block_xp\local\xp\described_level;
use block_xp\local\xp\state;

/**
 * Level-less group state.
 *
 * Simple implementation where the level is not computed.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelless_group_state implements renderable, state {

    /** @var stdClass The group object. */
    protected $group;
    /** @var int The group's XP. */
    protected $xp;

    /**
     * Constructor.
     *
     * @param stdClass $group The group object.
     * @param int $xp The group XP.
     */
    public function __construct(stdClass $group, $xp) {
        $this->group = $group;
        $this->xp = $xp;
    }

    public function get_id() {
        return $this->group->id;
    }

    public function get_level() {
        return new described_level(1, 1, '');
    }

    public function get_ratio_in_level() {
        return 1;
    }

    public function get_total_xp_in_level() {
        1;
    }

    /**
     * Return the group object.
     *
     * @return stdClass
     */
    public function get_group() {
        return $this->group;
    }

    public function get_xp() {
        return $this->xp;
    }

    public function get_xp_in_level() {
        return 1;
    }

}
