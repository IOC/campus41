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
 * Materials related management form.
 *
 * @package    local
 * @subpackage materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class material_edit_form extends moodleform {

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $material = $this->_customdata['data'];
        $categoryid = $this->_customdata['categoryid'];
        $courses = $this->_customdata['courses'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];

        $select = $mform->addElement('select', 'courseid', get_string('courses'), $courses);

        if (isset($material->courseid)) {
            $select->setselected($material->courseid);
        }

        $mform->addElement('filemanager', 'attachment_filemanager', get_string('attachment', 'glossary'), null, $attachmentoptions);

        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);
        $mform->setDefault('categoryid', $categoryid);

        $this->add_action_buttons();

        $this->set_data($material);

    }

    public function validation($data, $files) {
        global $DB;

        $errors = array();

        if (!$data['id']) {
            if ($DB->get_records('local_materials', array('courseid' => $data['courseid']))) {
                $errors['attachment_filemanager'] = get_string('duplicatedcourse', 'local_materials');
            }
        }

        return $errors;
    }

}

