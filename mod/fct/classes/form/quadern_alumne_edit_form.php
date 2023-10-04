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
 * FCT quader alumne related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_alumne_edit_form extends moodleform {

    public $class = 'fct_quadern_alumne';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $user = $DB->get_record('user', array('id' => $data->alumne));
        $fullname = fullname($user);
        $mform->addElement('static', 'fct_nom_alumne', get_string('name'), $fullname);

        $attributes = array('size' => 16);
        $mform->addElement('text', 'dni', get_string('dni', 'mod_fct'), $attributes);
        $mform->setType('dni', PARAM_TEXT);

        // Field data_naixement.
        $attributes = array('size' => 48);
        $mform->addElement('date_selector', 'data_naixement', get_string('data_naixement', 'mod_fct'), $attributes);

        $attributes = array('size' => 48);
        $mform->addElement('text', 'adreca', get_string('adreca', 'mod_fct'), $attributes);
        $mform->setType('adreca', PARAM_TEXT);

        $attributes = array('size' => 48);
        $mform->addElement('text', 'poblacio', get_string('poblacio', 'mod_fct'), $attributes);
        $mform->setType('poblacio', PARAM_TEXT);

        $attributes = array('size' => 8);
        $mform->addElement('text', 'codi_postal', get_string('codi_postal', 'mod_fct'), $attributes);
        $mform->setType('codi_postal', PARAM_TEXT);

        $attributes = array('size' => 48);
        $mform->addElement('text', 'telefon', get_string('telefon', 'mod_fct'), $attributes);
        $mform->setType('telefon', PARAM_TEXT);

        $attributes = array('size' => 48);
        $mform->addElement('text', 'email', get_string('email', 'mod_fct'), $attributes);
        $mform->setType('email', PARAM_TEXT);

        $mform->addElement('select', 'procedencia', get_string('procedencia', 'mod_fct'), $data->procedencies);

        $attributes = array('size' => 48);
        $maxbytes = 0;
        $maxfiles = 1;
        $subdirs = false;
        $imageoptions = array(
            'maxbytes' => $maxbytes,
            'maxfiles' => $maxfiles,
            'subdirs' => $subdirs,
            'accepted_types' => array('web_image'),
        );
        $mform->addElement('text', 'targeta_sanitaria', get_string('targeta_sanitaria', 'mod_fct'), $attributes);
        $mform->setType('targeta_sanitaria', PARAM_TEXT);
        $mform->addElement('filemanager', 'targetaimage_filemanager', get_string('imatge_targeta', 'fct'), null, $imageoptions);

        $attributes = array('size' => 48);
        $mform->addElement('text', 'inss', get_string('inss', 'mod_fct'), $attributes);
        $mform->setType('inss', PARAM_TEXT);
        $mform->addElement('filemanager', 'inssimage_filemanager', get_string('imatge_targeta', 'fct'), null, $imageoptions);

        $mform->addRule('email', get_string('validacio_email', 'fct'), 'email', null, 'client');

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