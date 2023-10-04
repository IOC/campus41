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
 * Renderers for outputting fct qualificacio.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_qualificacio_renderer extends plugin_renderer_base {

    public function view($quadern) {

        $output = '';

        $quadern->qualificaciotype == 'global' ? $qualificacio = 'qualificacio_global' : $qualificacio = 'qualificacio';

        $qualificacions = $quadern->qualificacions();
        $barems = $quadern->barem_valoracio();

        if ($quadern->apte) {
            $apte = $qualificacions[$quadern->apte];
        } else {
            $apte = '';
        }
        if ($quadern->nota) {
            $nota = $barems[$quadern->nota];
        } else {
            $nota = '';
        }

        $output .= html_writer::start_div('databox');

        $lastquadern = array();
        if ($quadern->qualificaciotype == 'global') {
            $lastquadern = fct_ultim_quadern($quadern->alumne, $quadern->cicle);
        }
        if (empty($lastquadern)) {
            $lastquadern = (object) array ('id' => $quadern->id);
        }

        if ($lastquadern->id == $quadern->id) {
            $output .= html_writer::start_div('datagroup');
            $output .= html_writer::tag('span', get_string('qualificacio', 'fct').':', array('class' => 'datatitle'));
            $output .= html_writer::tag('span', $apte, array('class' => 'datacontent'));
            $output .= html_writer::end_div();

            $output .= html_writer::start_div('datagroup');
            $output .= html_writer::tag('span', '', array('class' => 'datatitle'));
            $output .= html_writer::tag('span', $nota, array('class' => 'datacontent'));
            $output .= html_writer::end_div();

            $output .= html_writer::start_div('datagroup');
            $output .= html_writer::tag('span', get_string('data', 'fct').':', array('class' => 'datatitle'));

            if ($quadern->qualificacio->data) {
                $output .= html_writer::tag('span', userdate($quadern->data, get_string('strftimedate')), array('class' => 'datacontent'));
            }
            $output .= html_writer::end_div();

            $output .= html_writer::start_div();
            $output .= html_writer::tag('span', get_string('observacions', 'fct').':', array('class' => 'datatitle'));
            $output .= html_writer::tag('span', nl2br($quadern->observacions), array('class' => 'datacontent'));
            $output .= html_writer::end_div();

            if ($quadern->checkpermissions('editlink')) {
                $cm = get_coursemodule_from_instance('fct', $quadern->fct);
                $link = new moodle_url('./edit.php', array('cmid' => $cm->id,
                                        'quadern' => $quadern->id,
                                        'page' => 'quadern_valoracio',
                                        'subpage' => 'quadern_qualificacio',
                                        'qualificaciotype' => $quadern->qualificaciotype));

                $output .= html_writer::start_div('fct_actions');
                $output .= html_writer::link($link, get_string('edit'), array('class' => 'datalink'));
                $output .= html_writer::end_div();
            }

        } else {
            $params = array(
                'id' => $this->page->cm->id,
                'quadern' => $lastquadern->id,
                'page' => 'quadern_qualificacio',
                'qualificaciotype' => 'global',
            );
            $output .= html_writer::start_div('fct_message');
            $url = new moodle_url('./view.php', $params);
            $output .= html_writer::tag('span', get_string('qualificacio_global_es_troba', 'fct'));
            $output .= html_writer::link($url, get_string('qualificacio_global_a_ultim_quadern', 'fct'), array('class' => 'datalink'));
            $output .= html_writer::end_div();
        }

        $output .= html_writer::end_div();

        echo $output;
    }
}