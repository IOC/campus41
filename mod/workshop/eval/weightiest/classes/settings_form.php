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
 * Settings form.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopeval_weightiest
 */

namespace workshopeval_weightiest;

defined('MOODLE_INTERNAL') || die();

/**
 * Settings form.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopeval_weightiest
 */
class settings_form extends \workshop_evaluation_settings_form {

    /**
     * Definition of evaluation settings.
     */
    protected function definition_sub() {
        $mform = $this->_form;
        $current = $this->_customdata['current'];

        $options = array();
        for ($i = 0; $i <= 9; $i++) {
            $options[$i] = get_string('comparisonlevel' . $i, 'workshopeval_weightiest');
        }
        $mform->addElement('select', 'comparison', get_string('comparison', 'workshopeval_weightiest'), $options);
        $mform->setDefault('comparison', 5);

        $this->set_data($current);
    }
}
