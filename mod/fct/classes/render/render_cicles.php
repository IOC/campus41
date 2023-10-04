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
 * Renderers for outputting cicles.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_cicles_renderer extends plugin_renderer_base {

    public function cicles_table($cicles) {
        $data = array();

        if ($cicles) {
            foreach ($cicles as $record) {
                $cicle = new fct_cicle($record->id);
                $data[] = $this->make_table_line($cicle);
            }
        }
        $table = new html_table();
        $table->head = array(get_string('nom', 'mod_fct'), get_string('edit'));
        $table->data = $data;
        $table->id = 'cicles';
        $table->attributes['class'] = 'cicles generaltable';
        $table->colclasses = array('', 'edit');

        $output = html_writer::table($table);
        return $output;

    }

    private function make_table_line($cicle) {
        global $DB, $OUTPUT;

        $cm = get_coursemodule_from_instance('fct', $cicle->fct);

        $line = array();
        $line[] = format_string($cicle->nom);
        $buttons = array();
        $editlink = new moodle_url('./edit.php', array('cmid' => $cm->id, 'id' => $cicle->id, 'page' => 'cicle'));
        $editicon = html_writer::empty_tag('img',
            array('src' => $OUTPUT->image_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall'));
        $buttons[] = html_writer::link($editlink, $editicon);
        if ($cicle->checkpermissions('deletelink')) {
            $deletelink = new moodle_url('./edit.php', array('cmid' => $cm->id, 'id' => $cicle->id, 'delete' => 1, 'page' => 'cicle'));
            $deleteicon = html_writer::empty_tag('img',
                array('src' => $OUTPUT->image_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall'));
            $buttons[] = html_writer::link($deletelink, $deleteicon);

        }
        $line[] = implode(' ', $buttons);
        return $line;
    }

}
