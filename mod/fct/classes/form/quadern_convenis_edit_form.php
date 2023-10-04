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
 * FCT convenis related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_convenis_edit_form extends moodleform {

    public $class = 'fct_quadern_conveni';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        if (isset($data->convenis)) {
            foreach ($data->convenis as $conveni) {

                $mform->addElement('header', $conveni->uuid.'_headerconveni', get_string('conveni', 'mod_fct').' '.$conveni->codi);
                $mform->addElement('text', $conveni->uuid.'_codi', get_string('codi', 'mod_fct'));
                $mform->setType($conveni->uuid.'_codi', PARAM_RAW);

                $mform->addElement('date_selector', $conveni->uuid.'_data_inici', get_string('data_inici', 'mod_fct'));

                $mform->addElement('date_selector', $conveni->uuid.'_data_final', get_string('data_final', 'mod_fct'));
                $mform->addElement('checkbox', $conveni->uuid.'_delete_conveni', get_string('delete'));
            }
        }

        $mform->addElement('header', 'new_headerconveni', get_string('nou_conveni', 'mod_fct'));

        $mform->addElement('text', 'new_codi', get_string('codi', 'mod_fct'));
        $mform->setType('new_codi', PARAM_RAW);

        $mform->addElement('date_selector', 'new_data_inici', get_string('data_inici', 'mod_fct'));

        $mform->addElement('date_selector', 'new_data_final', get_string('data_final', 'mod_fct'));

        $mform->addElement('header', 'general', get_string('general', 'mod_fct'));

        $mform->addElement('textarea', 'prorrogues', get_string('prorrogues', 'mod_fct'));
        $mform->addElement('text', 'hores_practiques', get_string('hores_practiques', 'mod_fct'));
        $mform->setType('hores_practiques', PARAM_INT);

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

    public function set_data($data) {

        if ($data->convenis) {
            foreach ($data->convenis as $conveni) {
                $uuiddatainici = $conveni->uuid.'_data_final';
                $data->$uuiddatainici = $conveni->data_final;

                $uuiddatainici = $conveni->uuid.'_data_inici';
                $data->$uuiddatainici = $conveni->data_inici;

                $uuidcodi = $conveni->uuid.'_codi';
                $data->$uuidcodi = $conveni->codi;
            }
        }
        parent::set_data($data);
    }
}

