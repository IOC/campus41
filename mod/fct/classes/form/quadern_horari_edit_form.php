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
 * FCT horari related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_horari_edit_form extends moodleform {

    public $class = 'fct_quadern_horari';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('select', 'conveni', get_string('conveni', 'mod_fct'), $data->convenis);

        $mform->addElement('select', 'dies', get_string('dies', 'mod_fct'), $data->dies);

        $horesfrom[] =& $mform->createElement('select', 'hourfrom', get_string('hores', 'mod_fct'), $this->hours());
        $horesfrom[] =& $mform->createElement('static', 'fct_separator_start', '', ':');
        $horesfrom[] =& $mform->createElement('select', 'minutfrom', get_string('minuts', 'mod_fct'), $this->minuts());

        $mform->addGroup($horesfrom, 'horesfrom', get_string('de', 'mod_fct'), array(''), false);

        $horesto[] =& $mform->createElement('select', 'hourto', get_string('hores', 'mod_fct'), $this->hours());
        $horesto[] =& $mform->createElement('static', 'fct_separator_end', '', ':');
        $horesto[] =& $mform->createElement('select', 'minutto', get_string('hores', 'mod_fct'), $this->minuts());

        $mform->addGroup($horesto, 'horesfrom', get_string('a', 'mod_fct'), array(''), false);

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
        $this->set_data($data);
    }

    public function validation($data, $files) {
        $errors = array();
        $class = $this->class;
        $errors = $class::validation($data);
        return $errors;
    }

    private function hours() {
        $hours = array();

        for ($hour = 0; $hour < 24; $hour++) {
            $hours[$hour] = (string) $hour;
        }
        return $hours;
    }

    private function minuts() {

        $minuts = array();
        for ($minut = 0; $minut < 60; $minut += 15) {
            $minuts[$minut] = sprintf("%02d", $minut);
        }
        return $minuts;
    }
}

