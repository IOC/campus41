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
 * Renderer for materials local plugin
 *
 * @package    local
 * @subpackage materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_materials_renderer extends plugin_renderer_base {

    public function search_form($searchquery) {

        $search  = html_writer::start_tag('form', array('id' => 'searchmaterialquery', 'method' => 'get'));
        $search .= html_writer::start_tag('div', array('class' => 'materials_search'));
        $search .= html_writer::label(get_string('searchmaterial', 'local_materials'), 'material_search_q'); // No : in form labels!
        $params = array(
                        'id' => 'material_search_q',
                        'type' => 'text',
                        'name' => 'search',
                        'value' => $searchquery,
                        'maxlength' => '50',
                        'class' => 'materials_text_search',
        );
        $search .= html_writer::empty_tag('input', $params);
        $search .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('search')));
        $search .= html_writer::end_tag('div');
        $search .= html_writer::end_tag('form');
        return $search;

    }

    private function make_table_line($material) {
        global $DB, $OUTPUT;

        $line = array();
        $course = $DB->get_record('course', array('id' => $material->courseid));
        $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
        $line[] = html_writer::link($courseurl, $course->shortname);
        $line[] = html_writer::link($courseurl, $course->fullname);
        $line[] = $this->make_sources_field($material->sources);
        $buttons = array();
        $editlink = new moodle_url('./edit.php', array('id' => $material->id, 'categoryid' => $course->category));
        $editicon = html_writer::empty_tag('img',
            array('src' => $OUTPUT->image_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall'));
        $deletelink = new moodle_url('./edit.php', array('id' => $material->id, 'categoryid' => $course->category, 'delete' => 1));
        $deleteicon = html_writer::empty_tag('img',
            array('src' => $OUTPUT->image_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall'));
        $buttons[] = html_writer::link($editlink, $editicon);
        $buttons[] = html_writer::link($deletelink, $deleteicon);
        $line[] = implode(' ', $buttons);

        return $line;
    }

    private function make_sources_field($sources) {
        global $OUTPUT;

        $files = unserialize($sources);
        $stringsources = '';
        foreach ($files as $key => $value) {

            $filename = explode('/', $value);
            if (!preg_match('/^.+?\.\w+$/', end($filename))) {
                $stringsourcesfolders[] = html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/files'),
                                                               'alt' => get_string('edit'),
                                                               'class' => 'iconsmall')).end($filename)."\n";
            } else {
                $stringsourcesfiles[] = html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/report'),
                                                               'alt' => get_string('edit'),
                                                               'class' => 'iconsmall')).end($filename)."\n";
            }
        }
        if (isset($stringsourcesfolders)) {
            foreach ($stringsourcesfolders as $stringsourcesfolder) {
                $stringsources .= $stringsourcesfolder;
            }
        }
        if (isset($stringsourcesfiles)) {
            foreach ($stringsourcesfiles as $stringsourcesfile) {
                $stringsources .= $stringsourcesfile;
            }
        }
        return $stringsources;
    }

    public function materials_table($materials) {
        $data = array();

        if ($materials) {
            if ($materials['total'] > 0) {
                foreach ($materials['records'] as $material) {
                    $data[] = $this->make_table_line($material);
                }
            } else {
                return get_string('nomaterials', 'local_materials');
            }
        }

        $table = new html_table();
        $table->head = array(get_string('shortname'), get_string('course'), get_string('sources', 'local_materials'), get_string('edit'));
        $table->data = $data;
        $table->id = 'materials';
        $table->attributes['class'] = 'admintable generaltable';

        $output = html_writer::table($table);
        return $output;

    }
}