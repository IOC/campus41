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
 * Renderers for outputting fct quadern empresa.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

class mod_fct_quadern_empresa_renderer extends plugin_renderer_base {

    public function view($quadern) {

        $output = '';

        $output .= html_writer::start_div('databox');

        $output .= html_writer::tag('span', get_string('empresa', 'fct'), array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('nom', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nom_empresa, array('class' => 'datacontent'));
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
        $output .= html_writer::tag('span', get_string('fax', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->fax, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('email', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->email, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('nif', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nif, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('codi_agrupacio', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->codi_agrupacio, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('sic', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->sic, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::end_div();

        $output .= html_writer::start_div('databox');

        $output .= html_writer::tag('span', get_string('responsable_conveni', 'fct'), array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('nom', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nom_responsable, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('cognoms', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->cognoms_responsable, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('dni', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->dni_responsable, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('carrec', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->carrec_responsable, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::end_div('databox');

        $output .= html_writer::start_div('databox');

        $output .= html_writer::tag('span', get_string('tutor_empresa', 'fct'), array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('nom', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nom_tutor, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('cognoms', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->cognoms_tutor, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('dni', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->dni_tutor, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('email', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->email_tutor, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::end_div('databox');

        $output .= html_writer::start_div('databox');

        $output .= html_writer::tag('span', get_string('lloc_practiques', 'fct').':', array('class' => 'databoxtitle'));

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('nom', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->nom_lloc_practiques, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('adreca', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->adreca_lloc_practiques, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('poblacio', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->poblacio_lloc_practiques, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('codi_postal', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->codi_postal_lloc_practiques, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('datagroup');
        $output .= html_writer::tag('span', get_string('telefon', 'fct').':', array('class' => 'datatitle'));
        $output .= html_writer::tag('span', $quadern->telefon_lloc_practiques, array('class' => 'datacontent'));
        $output .= html_writer::end_div();

        if ($quadern->checkpermissions('editlink')) {
            $cm = get_coursemodule_from_instance('fct', $quadern->fct);
            $output .= html_writer::start_div('fct_actions');
            $params = array('cmid' => $cm->id,
                            'quadern' => $quadern->id,
                            'page' => 'quadern_dades',
                            'subpage' => 'quadern_empresa');
            $link = new moodle_url('./edit.php', $params);
            $output .= html_writer::link($link, get_string('edit'), array('class' => 'datalink'));
            $output .= html_writer::end_div();
        }

        $output .= html_writer::end_div('databox');

        echo $output;

    }

}
