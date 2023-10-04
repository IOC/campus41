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
 * FCT quadern search form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_search_form extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $mform->updateAttributes(array('autocomplete' => 'on'));
        $searchdata = $this->_customdata['searchdata'];

        $elements[] =& $mform->createElement('static', '', '', get_string('curs', 'mod_fct').': ');
        $elements[] =& $mform->createElement('select', 'searchcurs', get_string('curs', 'mod_fct'), $searchdata->cursos, array('class' => 'fct_search_elem'));
        $elements[] =& $mform->createElement('static', '', ' ', get_string('cicle', 'mod_fct').': ');
        $elements[] =& $mform->createElement('select', 'searchcicle', get_string('cicle', 'mod_fct'), $searchdata->cicles, array('class' => 'fct_search_elem'));
        $elements[] =& $mform->createElement('static', '', ' ', get_string('estat', 'mod_fct').': ');
        $elements[] =& $mform->createElement('select', 'searchestat', get_string('estat', 'mod_fct'), $searchdata->estats, array('class' => 'fct_search_elem'));
        $elements[] =& $mform->createElement('static', '', ' ', get_string('cerca', 'mod_fct').': ');
        $elements[] =& $mform->createElement('text', 'cerca', get_string('cerca', 'mod_fct'), array('class' => 'fct_search_elem'));
        $mform->setType('cerca', PARAM_TEXT);

        $mform->addGroup($elements, 'searchfields', '', array(''), false);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->set_data($searchdata);

        $mform->disable_form_change_checker();
    }

    public function set_data($data) {
        $data->searchcurs = $data->curs;
        $data->searchcicle = $data->cicle;
        $data->searchestat = $data->estat;

        parent::set_data($data);
    }
}