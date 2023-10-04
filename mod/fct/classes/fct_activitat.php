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
 * Activitat fct class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_edit_form.php');
require_once('fct_base.php');
require_once('fct_cicle.php');

class fct_activitat extends fct_base{

    public $id;
    public $fct;
    public $quadern;
    public $objecte;


    protected static $table = 'fct_quadern';
    protected $record_keys = array('id', 'quadern', 'objecte');
    protected $objecte_keys = array('id', 'alumne', 'tutor_centre', 'tutor_empresa', 'nom_empresa', 'cicle', 'estat', 'data_final', 'fct');
    protected $editform = 'fct_quadern_edit_form';


    public function view($id = false) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quaderns');
        if (!$id) {

            $fctid = $this->fct;
            $records = self::get_records($fctid);

            $table = $output->quaderns_table($records);
            echo $table;
        }
    }

    public static function get_records($fctid, $userid = null, $searchparams = false, $pagenumber = false) {
        global $DB;

        $records = new stdClass;

        $cicles = fct_cicle::get_records($fctid);

        $ciclesid = array();

        foreach ($cicles as $cicle) {
            $ciclesid[] = $cicle->id;
        }

        $records = $DB->get_records_list('fct_quadern', 'cicle', array_keys($cicles));

        return $records;

    }

    public function prepare_form_data($data) {
        global $DB;

        if (!isset($this->fct)) {
            print_error('nofct');
        } else {
            $cm = get_coursemodule_from_instance('fct', $this->fct);
            $context = context_course::instance($cm->course);

            $alumnes = get_role_users(5, $context);
            foreach ($alumnes as $alumne) {
                $alumne->fullname = fullname($alumne);
            }

            $data->alumne = $this->prepare_form_select($alumnes, 'id', 'fullname', $data->alumne);
            $cicles = fct_cicle::get_records($this->fct);
            $data->cicle = $this->prepare_form_select($cicles, 'id', 'nom', $data->cicle);

            $roleid = $DB->get_field('role', 'id', array('shortname' => 'tutorempresa'));
            $tutorsempresa = get_role_users($roleid, $context);

            foreach ($tutorsempresa as $tutorempresa) {
                $tutorempresa->fullname = fullname($tutorempresa);
            }

            $data->tutor_empresa = $this->prepare_form_select($tutorsempresa, 'id', 'fullname', $data->tutor_empresa);

            $context = context_module::instance($cm->id);
            $records = get_users_by_capability($context, 'mod/fct:tutor_centre');
            foreach ($records as $record) {
                $record->fullname = fullname($record);
            }

            $data->tutor_centre = $this->prepare_form_select($records, 'id', 'fullname', $data->tutor_centre);

            $data->estat = $this->estats;

        }
    }

    protected function prepare_form_select($objects, $selectkey, $selectvalue, $selected = false) {
        $select = array();

        if (!$selected) {
            $select[0] = '';
        }

        foreach ($objects as $object) {
            $select[$object->$selectkey] = $object->$selectvalue;
        }

        return $select;

    }

    public static function validation($data) {
    }

}