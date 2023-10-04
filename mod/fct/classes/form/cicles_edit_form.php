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
 * FCT cicles related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_cicle_edit_form extends moodleform {

    public $class = 'fct_cicle';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $attributes = array('size' => 48);
        $mform->addElement('text', 'nom', get_string('nom', 'mod_fct'), $attributes);
        $mform->addRule('nom', null, 'required');
        $mform->setType('nom', PARAM_TEXT);

        $attributes = array('cols' => 60, 'rows' => 20);
        $mform->addElement('textarea', 'activitats', get_string("activitats", "mod_fct"), $attributes);
        $mform->setType('activitats ', PARAM_TEXT);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'objecte');
        $mform->setType('objecte', PARAM_TEXT);
        $mform->setDefault('objecte', '');

        $this->add_action_buttons();
        $this->set_data($data);
    }

    public function validation($data, $files) {
        $errors = array();
        $class = $this->class;
        $errors = $class::validation($data);

        return $errors;
    }

    public function set_data($data) {
        if (isset($data->activitats) && is_array($data->activitats)) {
            $data->activitats = implode("\n" , $data->activitats);
        }
        parent::set_data($data);
    }

    public function get_data() {
        $data = parent::get_data();
        if (isset($data->activitats)) {
            $data->activitats = explode("\n", $data->activitats);
        }
        return $data;
    }
}