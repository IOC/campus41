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
 * Renderers for outputting fct dades relatives.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_dades_relatives_renderer extends plugin_renderer_base {

    public function view($quadern) {

        global $PAGE;

        $resum = $quadern->resum_hores_fct();

        $output = '';

        $output .= html_writer::start_div('databox');

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('hores_credit', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->hores_credit, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('exempcio', 'fct'). ':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->exempcio . '%', array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('hores_anteriors', 'fct'). ':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->hores_anteriors, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('hores_realitzades', 'fct'). ':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', get_string('hores_realitzades_detall', 'fct', $resum), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('hores_pendents', 'fct'). ':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $resum->pendents, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        if ($quadern->checkpermissions('editlink')) {
            $params = array('cmid' => $PAGE->cm->id,
                            'quadern' => $quadern->id,
                            'page' => 'quadern_dades',
                        '   subpage' => 'quadern_dades_relatives');
            $link = new moodle_url('./edit.php', $params);
            $output .= html_writer::start_div('fct_actions');
            $output .= html_writer::link($link, get_string('edit'), array('class' => 'datalink'));
            $output .= html_writer::end_div();
        }

        $output .= html_writer::end_div();

        echo $output;

    }

}
