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
 * FCT tutor related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_tutor_edit_form extends moodleform {

    private $class = 'fct_tutor';

    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('text', 'dni', get_string('dni', 'mod_fct'));
        $mform->addRule('dni', null, 'required');
        $mform->setType('dni', PARAM_TEXT);

        $mform->addElement('text', 'firstname', get_string('nom', 'fct'));
        $mform->addRule('firstname', null, 'required');
        $mform->setType('firstname', PARAM_TEXT);

        $mform->addElement('text', 'lastname', get_string('cognoms', 'fct'));
        $mform->addRule('lastname', null, 'required');
        $mform->setType('lastname', PARAM_TEXT);

        $mform->addElement('text', 'email', get_string('email', 'fct'));
        $mform->addRule('email', null, 'required');
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', get_string('validacio_email', 'fct'), 'email', null, 'client');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);

        $this->add_action_buttons(false);
        $this->set_data($data);

    }

    public function validation($data, $files) {
        $errors = array();
        $class = $this->class;
        $errors = $class::validation($data);
        return $errors;
    }
}
