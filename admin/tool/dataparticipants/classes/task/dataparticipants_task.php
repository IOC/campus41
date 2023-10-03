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
 * A scheduled task.
 *
 * @package    tool
 * @subpackage dataparticipants
 * @copyright  2019 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataparticipants\task;

defined('MOODLE_INTERNAL') || die();

define('AWEEK', 7 * 24 * 3600);
define('THREEMONTHS', 90 * 24 * 3600);

require_once($CFG->dirroot . '/admin/tool/dataparticipants/lib.php');

/**
 * Send data to configured email.
 */
class dataparticipants_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('dataparticipants_task', 'tool_dataparticipants');
    }

    /**
     * Execute tasks.
     */
    public function execute() {
        global $DB, $CFG;

        if (!isset($CFG->local_xtecmail_app) || !isset($CFG->local_xtecmail_sender) || !isset($CFG->local_xtecmail_env)) {
            return;
        }

        $utils = new \tool_dataparticipants_utils();
        $tasks = $DB->get_records('tool_dataparticipants');
        foreach ($tasks as $task) {
            $now = time();
            $send = false;
            if (is_null($task->timesend)) {
                $send = ($task->scheduled == WEEKLY) && ($task->timecreated + AWEEK <= $now);
                $send = $send || (($task->scheduled == QUARTERLY) && ($task->timecreated + THREEMONTHS <= $now));
            } else {
                $send = ($task->scheduled == WEEKLY) && ($task->timesend + AWEEK <= $now);
                $send = $send || (($task->scheduled == QUARTERLY) && ($task->timesend + THREEMONTHS <= $now));
            }
            if ($send) {
                if ($zipfile = $utils->generate_zip($task)) {
                    $utils->send_email($task, $zipfile);
                }
            }
        }
    }
}
