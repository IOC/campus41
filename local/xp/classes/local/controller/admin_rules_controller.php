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
 * Admin rules controller.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;
defined('MOODLE_INTERNAL') || die();

/**
 * Admin rules controller class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_rules_controller extends \block_xp\local\controller\admin_rules_controller {

    /**
     * Get available rules.
     *
     * @return array
     */
    protected function get_available_rules() {
        $config = \block_xp\di::get('config');
        $rules = [];

        $parentrules = parent::get_available_rules();
        foreach ($parentrules as $rule) {
            $rules[] = $rule;

            // We want to inject the completion rules right before this one. Note that we
            // cannot use instanceof as it matches on subclasses.
            if (get_class($rule->rule) == 'block_xp_rule_property') {
                array_splice($rules, -1, 0, [
                    (object) [
                        'name' => get_string('activitycompletion', 'completion'),
                        'rule' => new \local_xp\local\rule\activity_completion(),
                    ],
                    (object) [
                        'name' => get_string('coursecompletion', 'completion'),
                        'rule' => new \local_xp\local\rule\course_completion(),
                    ]
                ]);
            }

        }

        return $rules;
    }

}
