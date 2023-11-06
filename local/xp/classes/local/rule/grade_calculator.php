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
 * Calculator.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

/**
 * Calculator.
 *
 * Extracts the points off a grade. Note that this will produce strange behaviours
 * when the item is a scale item, but for performance reasons we do not care
 * about this just yet. Also, we round grades to the nearest integer, and do
 * not accept negative grades.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_calculator implements calculator {

    /**
     * Get the points for this subject.
     *
     * @param subject $subject The subject.
     * @return int Or null.
     */
    public function get_points(subject $subject) {
        if (!$subject instanceof event_subject) {
            return null;
        }

        $event = $subject->get_event();
        if (!$event instanceof \core\event\user_graded) {
            return null;
        } else if ($event->is_restored()) {
            // We can't call user_graded::get_grade on restore.
            return null;
        } else if (empty($event->other['itemid'])) {
            // We don't have a grade item ID? Good bye!
            return null;
        }

        $gradeobject = $event->get_grade();
        if (!$gradeobject) {
            $gradeobject = \grade_grade::fetch(['id' => $event->objectid]);
        }

        // Grade pre-checks.
        if (!$gradeobject) {
            // Never trust the gradebook!
            return null;
        } else if ($gradeobject->hidden) {
            // This check does not force the grade_item to be loaded, so let's give it a shot.
            // We do this because otherwise we would disclose the grade to the student.
            return null;
        } else if ($gradeobject->is_hidden()) {
            // This is the final check we should be doing before checking the grade_item, because
            // the grade item is loaded internally in grade_grade::is_hidden().
            return null;
        }

        // We should be good now.
        return $gradeobject->finalgrade !== null ? max(0, (int) $gradeobject->finalgrade) : null;
    }

}
