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
 * @package    tool
 * @subpackage dataparticipants
 * @copyright  2019 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class tool_dataparticipants_renderer extends plugin_renderer_base {

    public function tasks_table($tasks, $page=0) {
        $data = array();

        if (empty($tasks)) {
            return get_string('notasks', 'tool_dataparticipants');
        }

        $table = new html_table();
        $table->head = array(
                            get_string('courses', 'moodle'),
                            get_string('roles', 'moodle'),
                            get_string('email', 'moodle'),
                            get_string('interval', 'tool_dataparticipants'),
                            get_string('timesend', 'tool_dataparticipants'),
                            get_string('actions', 'moodle')
        );
        foreach ($tasks as $task) {
            $data[] = $this->task_row($task, $page);
        }
        $table->data = $data;
        $table->id = 'dataparticipants_task';
        $table->attributes['class'] = 'flexible admintable generaltable';

        $output = html_writer::table($table);
        return $output;
    }

    private function task_row($task, $page) {
        global $DB;

        $cells = $elements = array();

        list($sqlid, $params) = $DB->get_in_or_equal(explode(',', $task->courses));
        $courses = $DB->get_records_select('course', "id {$sqlid}", $params, '', 'id, shortname');
        foreach ($courses as $course) {
            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
            $elements[] = html_writer::link($courseurl, $course->shortname);
        }
        $cells[] = '<ul><li>' . implode('</li><li>', $elements) . '</li></ul>';

        $elements = array();
        list($sqlid, $params) = $DB->get_in_or_equal(explode(',', $task->roles));
        $roles = $DB->get_records_select('role', "id {$sqlid}", $params, '', 'name');
        foreach ($roles as $role) {
            $elements[] = html_writer::tag('span', $role->name);
        }
        $cells[] = '<ul><li>' . implode('</li><li>', $elements) . '</li></ul>';

        $cells[] = $task->email;

        $langstring = get_string('weekly', 'tool_dataparticipants');

        if ($task->scheduled == QUARTERLY) {
            $langstring = get_string('quarterly', 'tool_dataparticipants');
        }

        $cells[] = $langstring;

        if ($task->timesend) {
            $cells[] = userdate($task->timesend, get_string('strftimedatetime'));
        } else {
            $cells[] = get_string('notsendyet', 'tool_dataparticipants');
        }

        $buttons = array();
        $editlink = new moodle_url('dataparticipants_task.php', array('id' => $task->id, 'page' => $page));
        $buttons[] = $this->output->action_icon($editlink, new pix_icon('t/edit', get_string('edit')),
                null, array('class' => 'action-icon'));
        $deletelink = new moodle_url('dataparticipants_task.php', array('id' => $task->id, 'delete' => 1));
        $buttons[] = $this->output->action_icon($deletelink, new pix_icon('t/delete', get_string('delete')),
                null, array('class' => 'action-icon'));
        $cells[] = implode(' ', $buttons);

        return $cells;
    }
}
