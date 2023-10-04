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
 * Renderers for outputting fct activitats.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_activitat_renderer extends plugin_renderer_base {

    public function activitats_table($activitats) {

        $output = '';

        $data = array();

        if ($activitats) {
            foreach ($activitats as $activitat) {
                $data[] = $this->make_table_line($activitat);
            }
        }

        $table = new html_table();
        $heads = array(get_string('descripcio', 'mod_fct'));
        if (isset($activitat) && $activitat->checkpermissions('editlink')) {
            $heads[] = get_string('edit');
        }
        $table->head = $heads;
        $table->id = 'quaderns';
        $table->attributes['class'] = 'quadernactivitat generaltable';
        $table->colclasses = array('', 'edit');
        $table->data = $data;

        $output = html_writer::table($table);

        echo $output;

    }

    public function make_table_line($activitat) {
        global $OUTPUT, $PAGE;

        $line = array();

        $line[] = $activitat->descripcio;

        if ($activitat->checkpermissions('editlink')) {
            $editlink = new moodle_url('./edit.php', array('cmid' => $PAGE->cm->id, 'id' => $activitat->id, 'page' => 'quadern_activitat', 'quadern' => $activitat->quadern));
            $editicon = html_writer::empty_tag('img',
                array('src' => $OUTPUT->image_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall'));

            $buttons[] = html_writer::link($editlink, $editicon);

            $deletelink = new moodle_url('./edit.php', array('cmid' => $PAGE->cm->id,
                                                             'id' => $activitat->id,
                                                             'page' => 'quadern_activitat',
                                                             'quadern' => $activitat->quadern,
                                                             'delete' => 1));
            $deleteicon = html_writer::empty_tag('img',
                array('src' => $OUTPUT->image_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall'));
            $buttons[] = html_writer::link($deletelink, $deleteicon);

            $line[] = implode(' ', $buttons);
        }

        return $line;

    }

}
