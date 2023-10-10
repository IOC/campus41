<?php
// Local mail plugin for Moodle
// Copyright © 2013 Institut Obert de Catalunya
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
 * TinyMCE Proofreader tools plugin buttons and capabilities (only editing teachers can use it)
 *
 * @package   tinymce_proofreadertools
 * @copyright 2013 Institut Obert de Catalunya
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class tinymce_proofreadertools extends editor_tinymce_plugin {
    /** @var array list of buttons defined by this plugin */
    protected $buttons = array('correctoricon', 'blueicon', 'redicon', 'doubleslashicon');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {
        global $PAGE;

        $course = $PAGE->course;

        if (!$course || !$PAGE->cm || !has_capability('gradereport/grader:view', $context)) {
            return;
        }

        // Add button after 'image' in advancedbuttons2.
        $this->add_button_after($params, 2, 'correctoricon', 'spellcheck');
        $this->add_button_after($params, 2, 'blueicon', 'correctoricon');
        $this->add_button_after($params, 2, 'redicon', 'blueicon');
        $this->add_button_after($params, 2, 'doubleslashicon', 'redicon');

        // Add JS file, which uses default name.
        $this->add_js_plugin($params);
    }
}
