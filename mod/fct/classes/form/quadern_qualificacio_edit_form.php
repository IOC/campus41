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
 * FCT qualificacio related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_qualificacio_edit_form extends moodleform {

    public $class = 'fct_quadern_qualificacio';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $qualificacio = new $this->class($data->id);

        $mform->addElement('select', 'apte', get_string('qualificacio', 'mod_fct'), $qualificacio->qualificacions());
        $mform->addElement('select', 'nota', '', $qualificacio->barem_valoracio());
        $mform->addElement('date_selector', 'data', get_string('data', 'mod_fct'));
        $mform->addElement('textarea', 'observacions', get_string('observacions', 'mod_fct'), 'rows="4" cols="50" ');
        $mform->addElement('hidden', 'cmid');

        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'subpage');
        $mform->setType('subpage', PARAM_TEXT);
        $mform->addElement('hidden', 'quadern');
        $mform->setType('quadern', PARAM_TEXT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'objecte');
        $mform->setType('objecte', PARAM_TEXT);
        $mform->setDefault('objecte', '');
        $mform->addElement('hidden', 'qualificaciotype');
        $mform->setType('qualificaciotype', PARAM_TEXT);

        $this->add_action_buttons();
        $this->set_data($data);

    }

    public function validation($data, $files) {
        $errors = array();
        $class = $this->class;
        $errors = $class::validation($data);
        return $errors;
    }
}

