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
 * FCT frases related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_frases_edit_form extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $data->frases_centre = isset($data->frases_centre) ? implode($data->frases_centre, "\n") : '';
        $data->frases_empresa = isset($data->frases_empresa) ? implode($data->frases_empresa, "\n") : '';

        $attributes = array('cols' => 60, 'rows' => 20);
        $mform->addElement('textarea', 'frases_centre', get_string('tutor_centre', 'fct'), $attributes);
        $mform->setType('frases_centre', PARAM_TEXT);

        $attributes = array('cols' => 60, 'rows' => 20);
        $mform->addElement('textarea', 'frases_empresa', get_string('tutor_empresa', 'fct'), $attributes);
        $mform->setType('frases_empresa', PARAM_TEXT);

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
        $mform->setDefault('objecte', PARAM_TEXT);

        $this->add_action_buttons();
        $this->set_data($data);

    }
}