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
 * FCT quadern related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_edit_form extends moodleform {

    public $class = 'fct_quadern';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];
        if (empty($data->cicles)) {
            $html = '<center><strong>' . get_string('cicle_necessari_per_afegir_quaderns', 'fct') . '</strong></center>';
            $mform->addElement('html', $html);
            return;
        }

        if (!$data->es_administrador and $data->es_tutor_centre) {
            // Alumne
            $alumne = (isset($data->alumnes[$data->alumne])) ? $data->alumnes[$data->alumne] : '';
            $mform->addElement('static', null, get_string('alumne', 'mod_fct'), $alumne);

            // Nom empresa
            $attributes = array('size' => 48);
            $mform->addElement('text', 'nom_empresa', get_string('empresa', 'mod_fct'), $attributes);
            $mform->addRule('nom_empresa', null, 'required');
            $mform->setType('nom_empresa', PARAM_TEXT);

            // Cicle
            $mform->addElement('select', 'cicle', get_string('cicle', 'mod_fct'), $data->cicles);

            // Tutor centre
            $tutorcentre = (isset($data->tutors_centre[$data->tutor_centre])) ? $data->tutors_centre[$data->tutor_centre] : '';
            $mform->addElement('static', null, get_string('tutor_centre', 'mod_fct'), $tutorcentre);

            // Tutor empresa
            $tutorempresa = (isset($data->tutors_empresa[$data->tutor_empresa])) ? $data->tutors_empresa[$data->tutor_empresa] : '';
            $mform->addElement('static', null, get_string('tutor_empresa', 'mod_fct'), $tutorempresa);
        } else {
            // Alumne
            if (!$data->es_alumne) {
                $mform->addElement('select', 'alumne', get_string('alumne', 'mod_fct'), $data->alumnes);
            } else {
                $mform->addElement('hidden', 'alumne');
                $mform->setType('alumne', PARAM_INT);
            }

            // Nom empresa
            $attributes = array('size' => 48);
            $mform->addElement('text', 'nom_empresa', get_string('empresa', 'mod_fct'), $attributes);
            $mform->addRule('nom_empresa', null, 'required');
            $mform->setType('nom_empresa', PARAM_TEXT);

            // Cicle
            $mform->addElement('select', 'cicle', get_string('cicle', 'mod_fct'), $data->cicles);

            // Tutor centre
            $mform->addElement('select', 'tutor_centre', get_string('tutor_centre', 'mod_fct'), $data->tutors_centre);

            if (!$data->es_alumne) {
                // Tutor empresa
                $mform->addElement('select', 'tutor_empresa', get_string('tutor_empresa', 'mod_fct'), $data->tutors_empresa);
            }
        }

        // Estat
        if (!$data->es_alumne) {
            $mform->addElement('select', 'estat', get_string('estat', 'mod_fct'), $data->estats);
        } else {
            $mform->addElement('hidden', 'estat');
            $mform->setType('estat', PARAM_TEXT);
        }

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'quadern');
        $mform->setType('quadern', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'returnpage');
        $mform->setType('returnpage', PARAM_TEXT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'objecte');
        $mform->setType('objecte', PARAM_TEXT);
        $mform->setDefault('objecte', '');

        if (isset($data->id)) {
            $data->quadern = $data->id;
        }

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

