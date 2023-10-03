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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/dataparticipants/locallib.php');

/**
 * Form to manage from which courses will be collected participants data.
 *
 * @package    tool_dataparticipants
 * @copyright  2019 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dataparticipants_task_form extends moodleform {

    /**
     * Form definition
     * @return void
     */
    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata;

        $header = isset($data->id) ? 'edittask' : 'newtask';

        $mform->addElement('header', 'task', get_string($header, 'tool_dataparticipants'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $courses = tool_dataparticipants_utils::get_courses();

        $options = array(
            'multiple' => true
        );
        $select = $mform->addElement('autocomplete', 'courses', get_string('courses', 'moodle'), $courses, $options);
        $mform->addRule('courses', null, 'required', null, 'client');

        $rolenames = role_fix_names(get_all_roles(), context_system::instance(), ROLENAME_ORIGINAL);

        $rolenames = array_map(function($role) {
            return $role->name;
        }, $rolenames);

        $select = $mform->addElement('autocomplete', 'roles', get_string('roles', 'moodle'), $rolenames, $options);
        $mform->addRule('roles', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'moodle'), 'maxlength="100" size="30"');
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', null, 'required', null, 'client');

        $options = array(
            1 => get_string('weekly', 'tool_dataparticipants'),
            2 => get_string('quarterly', 'tool_dataparticipants'),
        );
        $mform->addElement('select', 'scheduled', get_string('interval', 'tool_dataparticipants'), $options);
        $mform->setType('scheduled', PARAM_INT);

        if (!isset($data->id)) {
            $mform->addElement('checkbox', 'sendnow', get_string('sendnow', 'tool_dataparticipants'));
        }

        $this->add_action_buttons();

        $this->set_data($data);
    }

    public function get_data() {
        $data = parent::get_data();
        if ($data !== null) {
            $data->courses = implode(',', $data->courses);
            $data->roles = implode(',', $data->roles);
        }
        return $data;
    }
}
