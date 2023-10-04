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
 * Renderers for outputting fct valoracio.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_valoracio_renderer extends plugin_renderer_base {

    public function view($valoracio) {

        $elements = $valoracio->valoraciollist;

        $output = '';

        $barems = $valoracio->barem_valoracio();

        $barem = $barems[0];
        $output .= html_writer::start_tag('div', array('class' => 'databox'));
        $valoraciotype = 'valoracio_'.$valoracio->valoracio;
        $valoracions = $valoracio->$valoraciotype;

        foreach ($elements as $key => $value) {

            if (isset($valoracions) && !empty($valoracions)) {
                $barem = is_array($valoracions) ? $barems[$valoracions[$key]] : $barems[$valoracions->$key];
            }

            $output .= html_writer::start_tag('div', array('class' => 'fitem'));
            $output .= html_writer::tag('div', $value, array('class' => 'fitemtitle'));
            $output .= html_writer::tag('div', $barem, array('class' => 'felement fstatic'));
            $output .= html_writer::end_tag('div');
        }

        $cm = get_coursemodule_from_instance('fct', $valoracio->fct);
        $link = new moodle_url('./edit.php', array('cmid' => $cm->id, 'quadern' => $valoracio->id, 'page' => 'quadern_valoracio', 'valoracio' => $valoracio->valoracio));
        $output .= html_writer::tag('div', '', array('class' => 'clearer'));
        $output .= html_writer::start_div('fct_actions fct_clear');
        if ($valoracio->checkpermissions('editlink')) {
            $output .= html_writer::link($link, get_string('edit'), array('class' => 'datalink'));
        }
        $output .= html_writer::end_div();

        $output .= html_writer::end_tag('div');

        return $output;

    }

}
