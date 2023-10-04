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
 * Renderers for outputting fct quadern main page.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_main_renderer extends plugin_renderer_base {

    public function view($quadern) {

        global $DB, $PAGE;

        $output = '';

        $output .= html_writer::start_div('databox');

        $user = $DB->get_record('user', array('id' => $quadern->alumne));
        $fullname = fullname($user);
        $userurl = new moodle_url('/user/view.php', array('id' => $quadern->alumne, 'course' => $PAGE->course->id));
        $userlink = html_writer::link($userurl, $fullname);

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('alumne', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $userlink, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('empresa', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nom_empresa, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('cicle', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nom_cicle(), array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $user = $DB->get_record('user', array('id' => $quadern->tutor_centre));
        $fullname = fullname($user);
        $userurl = new moodle_url('/user/view.php', array('id' => $quadern->tutor_centre, 'course' => $PAGE->course->id));
        $userlink = html_writer::link($userurl, $fullname);

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('tutor_centre', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $userlink, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $user = $DB->get_record('user', array('id' => $quadern->tutor_empresa));
        $fullname = fullname($user);
        $userurl = new moodle_url('/user/view.php', array('id' => $quadern->tutor_empresa, 'course' => $PAGE->course->id));
        $userlink = html_writer::link($userurl, $fullname);

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('tutor_empresa', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $userlink, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('estat', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern::$estats[$quadern->estat], array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::end_div('databox');

        $output .= html_writer::start_div('fct_actions');

        $cm = get_coursemodule_from_instance('fct', $quadern->fct);
        if ($quadern->usuari->es_administrador || $quadern->usuari->es_tutor_centre) {
            $params = array(
                'cmid' => $cm->id,
                'id' => $quadern->id,
                'returnpage' => 'quadern_main'
            );
            $editurl = new moodle_url('/mod/fct/edit.php', $params);
            $output .= html_writer::link($editurl, get_string('edit'));
        }

        if ($quadern->usuari->es_administrador ||
            ($quadern->usuari->es_tutor_centre && $quadern->estat == 'proposat')) {
            $params = array(
                'cmid' => $cm->id,
                'id' => $quadern->id,
                'delete' => '1'
            );
            $editurl = new moodle_url('/mod/fct/edit.php', $params);
            $output .= html_writer::link($editurl, get_string('delete'));
        }

        $params = array(
            'id' => $cm->id,
            'quadern' => $quadern->id,
            'page' => 'quadern_main',
            'action' => 'export_pdf',
        );
        $exporturl = new moodle_url('/mod/fct/view.php', $params);
        $output .= html_writer::link($exporturl, get_string('exporta_pdf', 'fct'));
        $output .= html_writer::end_div();
        echo $output;

    }

}
