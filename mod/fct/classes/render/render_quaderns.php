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
 * Renderers for outputting fct quaderns.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_quaderns_renderer extends plugin_renderer_base {

    public function quaderns_table($quaderns) {

        $data = array();

        if ($quaderns) {
            foreach ($quaderns as $quadern) {
                $data[] = $this->make_table_line($quadern);
            }
        }

        $table = new html_table();
        $heads = array(get_string('alumne', 'mod_fct'), get_string('empresa', 'mod_fct'),
                             get_string('cicle', 'mod_fct'), get_string('tutor_centre', 'mod_fct'),
                             get_string('tutor_empresa', 'mod_fct'), get_string('estat', 'mod_fct'),
                             get_string('data_final', 'mod_fct'));

        if (isset($quadern) && $quadern->checkpermissions('editlink')) {
            $heads[] = get_string('edit');
        }

        $table->head = $heads;
        $table->data = $data;
        $table->id = 'quaderns';
        $table->attributes['class'] = 'quaderns generaltable';
        $table->colclasses = array('', '', '', '', '', '', '', 'edit');

        $output = html_writer::table($table);

        return $output;

    }

    private function make_table_line($quadern) {
        global $DB, $OUTPUT, $USER;

        $cicle = new fct_cicle((int)$quadern->cicle);

        $cm = get_coursemodule_from_instance('fct', $cicle->fct);

        $line = array();

        // Quadern detail link
        $quadernlink = new moodle_url('./view.php', array('id' => $cm->id, 'quadern' => $quadern->id, 'page' => 'quadern_main'));

        // Username
        $user = $DB->get_record('user', array('id' => $quadern->alumne));
        $fullname = fullname($user);

        $line['alumne'] = html_writer::link($quadernlink, format_string($fullname));
        $line['empresa'] = html_writer::link($quadernlink, format_string($quadern->nom_empresa));
        $line['cicle'] = html_writer::link($quadernlink, $cicle->nom);

        $user = $DB->get_record('user', array('id' => $quadern->tutor_centre));
        $fullname = fullname($user);
        $line['tutor_centre'] = html_writer::link($quadernlink, format_string($fullname));

        $user = $DB->get_record('user', array('id' => $quadern->tutor_empresa));
        $fullname = fullname($user);
        $line['tutor_empresa'] = html_writer::link($quadernlink, format_string($fullname));
        $line['estat'] = html_writer::link($quadernlink, fct_quadern::$estats[$quadern->estat], array('class' => 'fct_' . $quadern->estat));
        $line['data_final'] = html_writer::link($quadernlink, userdate($quadern->data_final(), get_string('strftimedate')));

        $buttons = array();

        $usuari = new fct_usuari($cicle->fct, $USER->id);

        if ($quadern->checkpermissions('editlink')) {
            $editlink = new moodle_url('./edit.php', array('cmid' => $cm->id, 'id' => $quadern->id));
            $editicon = html_writer::empty_tag('img',
            array('src' => $OUTPUT->image_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall'));
            $buttons[] = html_writer::link($editlink, $editicon);
            if ($quadern->checkpermissions('deletelink')) {
                $deletelink = new moodle_url('./edit.php', array('cmid' => $cm->id, 'id' => $quadern->id, 'delete' => 1));
                $deleteicon = html_writer::empty_tag('img',
                    array('src' => $OUTPUT->image_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall'));
                $buttons[] = html_writer::link($deletelink, $deleteicon);
            }
            $line[] = implode(' ', $buttons);
        }

        return $line;
    }

    public function editlink($fctid, $userid) {
        global $PAGE;

        $user = new fct_usuari($fctid, $userid);
        $output = '';

        if ($user->es_alumne) {
            $editlink = new moodle_url('./edit.php', array('cmid' => $PAGE->cm->id));
            $output = html_writer::link($editlink, get_string('proposa_quadern', 'fct'));
        }

        return $output;
    }

}
