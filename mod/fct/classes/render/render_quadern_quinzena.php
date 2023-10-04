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
 * Renderers for outputting fct quinzenes.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

class mod_fct_quinzena_renderer extends plugin_renderer_base {

    public function quinzenes_table($quinzenes) {
        $data = array();

        foreach ($quinzenes as $quinzena) {
            $data[] = $this->make_table_line($quinzena);
        }

        $table = new html_table();
        $table->head = array(get_string('any', 'mod_fct'), get_string('periode', 'mod_fct'),
                             get_string('dies', 'mod_fct'), get_string('hores', 'mod_fct'),
                             get_string('edit'));
        $table->data = $data;
        $table->id = 'quinzenes';
        $table->attributes['class'] = 'quinzenes generaltable';
        $table->colclasses = array('', '', '', '', 'edit');

        $output = html_writer::table($table);
        return $output;

    }

    private function make_table_line($quinzena) {

        global $PAGE, $OUTPUT;

        $line = array();

        $params = array(
            'id' => $PAGE->cm->id,
            'quadern' => $quinzena->quadern,
            'page' => 'quadern_quinzena',
            'itemid' => $quinzena->id,
        );
        $viewlink = new moodle_url('./view.php', $params);

        $line['any'] = html_writer::link($viewlink, $quinzena->any);
        $line['periode'] = html_writer::link($viewlink, format_string($quinzena->nom_periode($quinzena->periode)));
        $line['dies'] = html_writer::link($viewlink, count($quinzena->dies));
        $line['hores'] = html_writer::link($viewlink, $quinzena->hores);

        $buttons = array();

        $params = array(
            'cmid' => $PAGE->cm->id,
            'id' => $quinzena->id,
            'quadern' => $quinzena->quadern,
            'page' => 'quadern_quinzena',
        );
        $editlink = new moodle_url('./edit.php', $params);

        if ($quinzena->checkpermissions('editlink')) {

            $params = array(
                'src' => $OUTPUT->image_url('t/edit'),
                'alt' => get_string('edit'),
                'class' => 'iconsmall',
            );
            $editicon = html_writer::empty_tag('img', $params);
            $params = array(
                'id' => $quinzena->id,
                'cmid' => $PAGE->cm->id,
                'delete' => 1,
                'page' => 'quadern_quinzena',
                'quadern' => $quinzena->quadern,
            );
            $buttons[] = html_writer::link($editlink, $editicon);
        }

        if ($quinzena->checkpermissions('deletelink')) {
            $deletelink = new moodle_url('./edit.php', $params);
            $deleteicon = html_writer::empty_tag('img',
                array('src' => $OUTPUT->image_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall'));

            $buttons[] = html_writer::link($deletelink, $deleteicon);
        }

        $line[] = implode(' ', $buttons);
        return $line;
    }

    public function resum_table($title, $lines) {
        $table = new html_table();
        $table->head = array($title,
                             get_string('dies', 'mod_fct'),
                             get_string('hores', 'mod_fct'));
        $table->data = $lines;
        $table->id = 'quinzenes';
        $table->attributes['class'] = 'admintable generaltable';

        $output = html_writer::table($table);

        echo $output;

    }

    public function data_prevista($dataprevista) {
        $text = get_string('data_prevista_valoracio_parcial', 'fct', userdate($dataprevista, get_string('strftimedate')));
        return html_writer::tag('div', $text, array('class' => 'fct_data_prevista'));
    }

    public function quinzena_detail($quinzena) {
        global $PAGE;

        $output = '';

        $output .= html_writer::start_div('databox');
        $output .= html_writer::tag('span', get_string('quinzena', 'fct'), array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('any', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quinzena->any, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('periode', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quinzena->nom_periode($quinzena->periode), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $dies = implode(',', $quinzena->dies);
        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('dies', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $dies, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('hores', 'fct').':', array('class' => 'datatitle'));

        $hores = floor($quinzena->hores);
        $minuts = round($quinzena->hores * 60);
        $minuts = $minuts % 60;
        $stringhores = $hores. ' ' . get_string('hores_i', 'fct'). ' ' . $minuts . ' ' . get_string('minuts', 'fct');

        $output .= html_writer::tag('span', $stringhores, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::tag('span', get_string('activitats_realitzades', 'fct'), array('class' => 'databoxtitle'));

        if ($activitats = $quinzena->activitats) {
            $output .= html_writer::start_tag('ul', array('class' => 'datalist'));
            foreach ($activitats as $activitat) {
                $quadernactivitat = new fct_quadern_activitat($activitat);
                $output .= html_writer::tag('li', $quadernactivitat->descripcio);
            }
            $output .= html_writer::end_tag('ul');
        } else {
            $output .= $this->notification(get_string('cap_activitat', 'fct'));
        }

        $output .= html_writer::tag('span', get_string('valoracions_observacions', 'fct'), array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('valoracions', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', nl2br($quinzena->valoracions), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('observacions', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', nl2br($quinzena->observacions_alumne), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::tag('span', get_string('retroaccio', 'fct'), array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('tutor_centre', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', nl2br($quinzena->observacions_centre), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('tutor_empresa', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', nl2br($quinzena->observacions_empresa), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('fct_actions');

        $params = array(
            'id' => $PAGE->cm->id,
            'quadern' => $quinzena->quadern,
            'page' => 'quadern_quinzena',
            );
        $returnurl = new moodle_url('./view.php', $params);
        $output .= html_writer::link($returnurl, get_string('return', 'fct'));

        if ($quinzena->checkpermissions('editlink')) {
            $params = array(
                'cmid' => $PAGE->cm->id,
                'id' => $quinzena->id,
                'quadern' => $quinzena->quadern,
                'page' => 'quadern_quinzena'
                );
            $editurl = new moodle_url('./edit.php', $params);
            $output .= html_writer::link($editurl, get_string('edit'));
        }

        if ($quinzena->checkpermissions('deletelink')) {
            $params = array(
                'cmid' => $PAGE->cm->id,
                'id' => $quinzena->id,
                'quadern' => $quinzena->quadern,
                'page' => 'quadern_quinzena',
                'delete' => 1,
                );

            $deleteurl = new moodle_url('./edit.php', $params);
            $output .= html_writer::link($deleteurl, get_string('delete'));
        }

        $output .= html_writer::end_div();

        $output .= html_writer::end_div(); // Databoxdiv.

        return $output;
    }

}
