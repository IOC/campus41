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
 * FCT quadern valoracio related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_valoracio_edit_form extends moodleform {

    public $class = 'fct_quadern_valoracio';

    public function definition() {

        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $starttag = '<span class="fct_valoracio">';
        $endtag = '</span>';
        foreach ($data->valoracions as $key => $value) {
            $linearray = array();
            $linearray[] = &$mform->createElement('static', 'valoracio_' . md5($key), '', $starttag . $value . $endtag);
            $linearray[] = &$mform->createElement('select', 'barem_' . md5($key), '', $data->barem);
            $mform->addGroup($linearray, 'line_'.$key, '', array(' '), false);
        }

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
        $mform->addElement('hidden', 'valoracio');
        $mform->setType('valoracio', PARAM_TEXT);

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

        $valoraciotype = 'valoracio_'.$data->valoracio;
        if (isset($data->$valoraciotype)) {
            foreach ((array)$data->$valoraciotype as $key => $value) {
                $baremkey = 'barem_'.md5($key);
                $data->$baremkey = $value;
            }
        }

        parent::set_data($data);
    }

    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            $valoracio = new $this->class((int)$data->quadern);
            $valoracio->valoracio = $data->valoracio;

            $valoracio->create_llist();

            $valoraciotype = 'valoracio_'.$valoracio->valoracio;

            $valoracioarray = array();
            foreach ($valoracio->valoraciollist as $key => $value) {
                if ($valoracio->valoracio = 'resultats') {
                    $baremkey = 'barem_'.md5($key);
                    $valoracioarray[(string)$key] = $data->$baremkey;
                } else {
                    $baremkey = 'barem_'.$key;
                    $valoracioarray[] = $data->$baremkey;
                }

            }

            $data->$valoraciotype = $valoracioarray;

        }
        return $data;
    }
}

