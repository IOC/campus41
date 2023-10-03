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
 * Data participants administration
 *
 * @package    tool
 * @subpackage dataparticipants
 * @copyright  2019 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/admin/tool/dataparticipants/lib.php');
require_once($CFG->dirroot.'/admin/tool/dataparticipants/dataparticipants_task_form.php');

require_login(SITEID, false);

$context = context_system::instance();
require_capability('tool/dataparticipants:manage', $context);


$id      = optional_param('id', 0, PARAM_INT);
$delete  = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$page    = optional_param('page', 0, PARAM_INT);

if ($id) {
    $task = $DB->get_record('tool_dataparticipants', array('id' => $id));
} else {
    $task = new StdClass;
}

$returnurl = new moodle_url('/admin/tool/dataparticipants/index.php', array('page' => $page));

$PAGE->set_context($context);
$params = array(
    'id' => $id,
    'delete' => $delete,
    'confirm' => $confirm,
    'page' => $page
);
$PAGE->set_url('/admin/tool/dataparticipants/dataparticipants_task.php', $params);

if ($delete and isset($task->id)) {
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('tool_dataparticipants', array('id' => $task->id));
        redirect($returnurl);
    }
    $strheading = get_string('removetask', 'tool_dataparticipants');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $params = array(
        'id' => $task->id,
        'delete' => 1,
        'confirm' => 1,
        'sesskey' => sesskey(),
        'page' => $page
    );
    $yesurl = new moodle_url('dataparticipants_task.php', $params);
    $message = get_string('confirmremovetask', 'tool_dataparticipants');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$strheading = isset($task->id) ? get_string('edit') : get_string('add');

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'tool_dataparticipants'));
$PAGE->navbar->add($strheading, new moodle_url('/admin/tool/dataparticipants/dataparticipants_task.php', $params));

$mform = new dataparticipants_task_form('', $task);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    if ($data->id) {
        $DB->update_record('tool_dataparticipants', $data);
    } else {
        $data->timecreated = time();
        $sendnow = (!empty($data->sendnow));
        unset($data->sendnow);
        $newid = $DB->insert_record('tool_dataparticipants', $data);
        if ($sendnow) {
            $utils = new tool_dataparticipants_utils();
            $data->id = $newid;
            if ($zipfile = $utils->generate_zip($data)) {
                $utils->send_email($data, $zipfile);
            }
        }
    }
    redirect(new moodle_url('/admin/tool/dataparticipants/index.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
echo $mform->display();
echo $OUTPUT->footer();
