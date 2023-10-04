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
 * FCT tancar quaderns related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2015 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_tancar_quaderns_form extends moodleform {

    private $class = 'fct_tancar_quaderns';

    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('static', 'quadernsoberts', get_string('quaderns_oberts', 'mod_fct'));
        $mform->addElement('static', 'quadernsproposats', get_string('quaderns_proposats', 'mod_fct'));

        $options = array(
            'tots' => get_string('tanca_tots', 'mod_fct'),
            'oberts' => get_string('tanca_oberts', 'mod_fct'),
            'proposats' => get_string('tanca_proposats', 'mod_fct'),
        );

        $mform->addElement('select', 'tancaquaderns', get_string('tancaquaderns', 'mod_fct'), $options);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);

        $this->add_action_buttons(false, get_string('submit'));
        $this->set_data($data);

    }

    public function validation($data, $files) {
        $errors = array();
        $class = $this->class;
        $errors = $class::validation($data);
        return $errors;
    }
}
