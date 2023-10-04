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
 * Centre related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_centre_edit_form extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $centredata = $data->centre;

        $mform->addElement('text', 'nom', get_string('nom', 'mod_fct'));
        $mform->setType('nom', PARAM_TEXT);

        $mform->addElement('text', 'adreca', get_string('adreca', 'fct'));
        $mform->setType('adreca', PARAM_TEXT);

        $attributes = array('size' => 8);
        $mform->addElement('text', 'codi_postal', get_string('codi_postal', 'fct'), $attributes);
        $mform->setType('codi_postal', PARAM_TEXT);

        $mform->addElement('text', 'poblacio', get_string('poblacio', 'fct'));
        $mform->setType('poblacio', PARAM_TEXT);

        $mform->addElement('text', 'telefon', get_string('telefon', 'fct'));
        $mform->setType('telefon', PARAM_TEXT);

        $mform->addElement('text', 'fax', get_string('fax', 'fct'));
        $mform->setType('fax', PARAM_TEXT);

        $mform->addElement('text', 'email', get_string('email', 'fct'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', get_string('validacio_email', 'fct'), 'email', null, 'client');

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
        $this->set_data($centredata);

    }
}