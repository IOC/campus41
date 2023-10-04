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
 * Renderers for outputting fct quadern alumne.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_alumne_renderer extends plugin_renderer_base {

    public function view($quadern) {

        global $DB, $PAGE;

        $output = '';

        $output .= html_writer::start_div('databox');

        $context = context_module::instance($PAGE->cm->id);
        $fs = get_file_storage();
        $targetaurl = $inssurl = '';

        if (!$fs->is_area_empty($context->id, 'mod_fct', 'targetaimage', $quadern->id, 'filename', false)) {
            $files = $fs->get_area_files($context->id, 'mod_fct', 'targetaimage', $quadern->id);
            foreach ($files as $file) {
                $targetaurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                              $file->get_component(),
                                                              $file->get_filearea(),
                                                              $file->get_itemid(),
                                                              $file->get_filepath(),
                                                              $file->get_filename());
            }
        }

        if (!$fs->is_area_empty($context->id, 'mod_fct', 'inssimage', $quadern->id, 'filename', false)) {
            $files = $fs->get_area_files($context->id, 'mod_fct', 'inssimage', $quadern->id);
            foreach ($files as $file) {
                $inssurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                                           $file->get_component(),
                                                           $file->get_filearea(),
                                                           $file->get_itemid(),
                                                           $file->get_filepath(), $file->get_filename());
            }
        }

        $user = $DB->get_record('user', array('id' => $quadern->alumne));
        $fullname = fullname($user);

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('nom', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $fullname, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('dni', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->dni, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('data_naixement', 'fct').':', array('class' => 'datatitle'));
        if ($quadern->data_naixement) {
            $date = userdate($quadern->data_naixement, get_string('strftimedate'));
        } else {
            $date = '';
        }
        $output .= html_writer::tag('span', $date, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('adreca', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->adreca, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('poblacio', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->poblacio, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('codi_postal', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->codi_postal, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('telefon', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->telefon, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('email', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->email, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        if ($quadern->procedencia) {
            $procedencies = $quadern->procedencies();
            $procedencia = $procedencies[$quadern->procedencia];
        } else {
            $procedencia = '';
        }
        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('procedencia', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $procedencia, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        // targeta_sanitaria
        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('targeta_sanitaria', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::start_div('datacontent');
        $output .= html_writer::tag('div', $quadern->targeta_sanitaria);
        if (!empty($targetaurl)) {
            $output .= html_writer::empty_tag('img', array('src' => $targetaurl, 'class' => 'fct_image'));
        }
        $output .= html_writer::end_div(); // datacontent
        $output .= html_writer::end_div();

        // inns
        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('inss', 'fct') . ':', array('class' => 'datatitle'));
        $output .= html_writer::start_div('datacontent');
        $output .= html_writer::tag('div', $quadern->inss);
        if (!empty($inssurl)) {
            $output .= html_writer::empty_tag('img', array('src' => $inssurl, 'class' => 'fct_image'));
        }
        $output .= html_writer::end_div(); // datacontent
        $output .= html_writer::end_div();

        if ($quadern->checkpermissions('editlink')) {
            $cm = get_coursemodule_from_instance('fct', $quadern->fct);
            $output .= html_writer::start_div('fct_actions');
            $params = array('cmid' => $cm->id,
                            'quadern' => $quadern->id,
                            'page' => 'quadern_dades',
                            'subpage' => 'quadern_alumne');
            $link = new moodle_url('./edit.php', $params);
            $output .= html_writer::link($link, get_string('edit'), array('class' => 'datalink'));
            $output .= html_writer::end_div('fct_actions');
        }

        $output .= html_writer::end_div();

        echo $output;

    }

}
