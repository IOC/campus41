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
 * Default course world config.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\config;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\config\immutable_config;
use block_xp\local\config\static_config;

/**
 * Default course world config.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class default_course_world_config extends immutable_config {

    /** The currency is not customised and uses the defaults from admin. */
    const CURRENCY_USE_DEFAULT = 0;
    /** The currency is custommised uses whatever is set in the course. */
    const CURRENCY_IS_CUSTOMIED = 1;

    /** The progress bar progresses towards the next level. */
    const PROGRESS_BAR_MODE_LEVEL = 0;
    /** The progress bar progresses towards the ultimate level. */
    const PROGRESS_BAR_MODE_OVERALL = 1;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(new static_config([
            'maxpointspertime' => 0,
            'timeformaxpoints' => 0,
            'currencystate' => self::CURRENCY_USE_DEFAULT,
            'badgetheme' => '',
            'enablegroupladder' => 0,
            'progressbarmode' => self::PROGRESS_BAR_MODE_LEVEL
        ]));
    }

}
