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
 * Fct mod dades centre class.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/llista_empreses_edit_form.php');
require_once($CFG->dirroot . '/mod/fct/classes/fct_quadern_empresa.php');
require_once($CFG->dirroot . '/mod/fct/classes/fct_cicle.php');
require_once($CFG->dirroot . '/lib/excellib.class.php');
require_once($CFG->dirroot . '/lib/filelib.php');

class fct_llista_empreses extends fct_base {

    public $id;
    public $course;
    public $name;
    public $intro;
    public $timecreated;
    public $timemodified;
    public $objecte;
    public $fct;

    public $frases_centre;
    public $frases_empresa;
    public $centre;

    protected static $table = 'fct';
    protected $editform = 'fct_llista_empreses_edit_form';

    public function tabs($id, $type = 'view') {
        $tab = parent::tabs_general($id);
        $tab['currentab'] = 'llista_empreses';

        return $tab;
    }

    public function set_data($data) {
        $this->centre = $data;
        parent::set_data($data);
    }

    public function prepare_form_data($data) {
        global $DB;

        if ($cicles = $DB->get_records('fct_cicle', array('fct' => $this->fct))) {
            $data->cicles = $cicles;
        }
    }

    public static function create_file_data($ciclesid) {
        global $DB;

        $camps = array(
            'nom',
            'adreca',
            'poblacio',
            'codi_postal',
            'telefon',
            'fax',
            'email',
            'nif',
            'cicle_formatiu',
            'tutor_empresa',
            'tutor_centre',
            'estat',
            'data_inici',
            'data_final',
        );

        $fctstring = function($value) {
            return get_string($value, 'fct');
        };

        $files = array(array_map($fctstring, $camps));

        foreach ($ciclesid as $cicleid) {
            $cicle = new fct_cicle((int)$cicleid);
            if ($quaderns = $DB->get_records('fct_quadern', array('cicle' => $cicle->id))) {
                foreach ($quaderns as $quadern) {
                    $user = $DB->get_record('user', array('id' => $quadern->tutor_centre));
                    $tutor_centre = fullname($user);
                    $user = $DB->get_record('user', array('id' => $quadern->tutor_empresa));
                    $tutor_empresa = fullname($user);
                    $empresa = new fct_quadern_empresa((int)$quadern->id);

                    $files[] = array(
                    $empresa->nom,
                    $empresa->adreca,
                    $empresa->poblacio,
                    $empresa->codi_postal,
                    $empresa->telefon,
                    $empresa->fax,
                    $empresa->email,
                    $empresa->nif,
                    $cicle->nom,
                    $tutor_empresa,
                    $tutor_centre,
                    get_string('estat_' . $empresa->estat, 'fct'),
                    $empresa->data_inici() ? userdate($empresa->data_final(), get_string('strftimedate')) : '-',
                    $empresa->data_final() ? userdate($empresa->data_final(), get_string('strftimedate')) : '-'
                    );
                }
            }
        }

        return $files;
    }

    public static function send_xls($files) {
        $workbook = new MoodleExcelWorkbook('-');
        $workbook->send(get_string('llista_empreses', 'fct') . '.xls');
        $worksheet = array();
        $worksheet = $workbook->add_worksheet(get_string('llista_empreses', 'fct'));
        foreach ($files as $fila => $columnes) {
            foreach ($columnes as $columna => $camp) {
                 $worksheet->write_string($fila, $columna, $camp);
            }
        }
        $workbook->close();
        die;
    }

}
