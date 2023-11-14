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
 * Webservice form theme CRE
 *
 * @package   theme_cre
 * @copyright 2018 Institut Obert de Catalunya
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');

$action   = required_param('action', PARAM_ALPHA);
$courseid = required_param('courseid', PARAM_INT);
$cmid     = required_param('cmid', PARAM_INT);
$task     = optional_param('task', -1, PARAM_INT);

require_sesskey(); // Gotta have the sesskey.
require_login($courseid, false); // Gotta be logged in (of course).

$response = new stdClass();
$element = 'theme_cre_todo_' . $courseid . '_' . $cmid;

switch ($action) {
    case "taskdone":
        if ($task >= 0) {
            $value = get_user_preferences($element, false, $USER);
            if (strlen($value) != 0) {
                $values = explode(',', $value);
                if (!in_array($task, $values)) {
                    $values[] = $task;
                }
                $values = implode(',', $values);
            } else {
                $values = $task;
            }
            set_user_preference($element, $values, $USER);
        }
        break;

    case "tasktodo":
        $value = get_user_preferences($element, false, $USER);
        if (strlen($value) != 0) {
            $values = explode(',', $value);
            $key = array_search($task, $values);
            if ($key !== false) {
                unset($values[$key]);
            }
            $values = implode(',', $values);
            set_user_preference($element, $values, $USER);
        }
        break;

    case "gettasks":
        $value = get_user_preferences($element, false, $USER);
        $response->values = '';
        if (strlen($value) != 0) {
            $response->values = explode(',', $value);
        }
        break;

    default:
        $response->error = 'Invalid action';
        break;
}

echo json_encode($response);
exit;