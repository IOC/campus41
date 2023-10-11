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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Edit form
 * http://docs.moodle.org/dev/
 *
 * @package    block_countdown
 * @copyright  Yevhen Matasar <matasar.ei@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_countdown_edit_form extends block_edit_form
{
    /**
     * Defines edit form
     *
     * @param MoodleQuickForm $mform
     *
     * @throws coding_exception
     */
    protected function specific_definition($mform)
    {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('countdown_title', 'block_countdown'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('text', 'config_url', get_string('countdown_url', 'block_countdown'));
        $mform->setType('config_url', PARAM_URL);

        $mform->addElement('select', 'config_urltarget', get_string('countdown_urltarget', 'block_countdown'), [
            '_self' => get_string('urltarget_self', 'block_countdown'),
            '_blank' => get_string('urltarget_blank', 'block_countdown')
        ]);
        $mform->addElement('date_time_selector', 'config_until', get_string('until', 'block_countdown'));

        $mform->addElement('text', 'config_ended_text', get_string('countdown_ended_text', 'block_countdown'));
        $mform->setType('config_ended_text', PARAM_TEXT);

        $mform->addElement('select', 'config_style', get_string('countdown_style', 'block_countdown'), [
            block_countdown::STYLE_DEFAULT => get_string('countdown_style_default', 'block_countdown'),
            block_countdown::STYLE_CORPORATE => get_string('countdown_style_corporate', 'block_countdown')
        ]);

        $mform->addElement('textarea', 'config_css', get_string("css", "block_countdown"), [
            'wrap' => "virtual",
            'rows' => "20",
            'cols' => "70"
        ]);
    }
}
