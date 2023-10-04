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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_fct_mod_form extends moodleform_mod {

    public function definition() {

        global $COURSE;
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', 'Nom', array('size' => '32'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', 'Quadern de pràctiques');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $this->standard_intro_elements(get_string('fctintro', 'fct'));

        $this->standard_coursemodule_elements(false);

        $this->add_action_buttons();
    }

}

