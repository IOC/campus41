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
 * Fct mod cicle class.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/cicles_edit_form.php');

class fct_cicle extends fct_base{

    public $id;
    public $fct;
    public $nom;
    public $activitats;
    protected $objecte;
    public $n_quaderns = 0;

    public $edit_form = 'fct_cicle_edit_form';

    protected static $table = 'fct_cicle';
    protected $record_keys = array('id', 'fct', 'nom', 'objecte');
    protected $objecte_keys = array('id', 'fct', 'nom', 'activitats');
    protected $propierties_keys = array('id', 'fct', 'nom', 'activitats', 'objecte');
    protected $editform = 'fct_cicle_edit_form';

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_general($id);

        $row = $tab['row'];

        $activerow = $row['cicle'];

        $activerow->subtree[] = new tabobject('ciclelist', new moodle_url('/mod/fct/view.php',
                                        array('id' => $id, 'page' => 'cicle')),
                                            get_string('cicles', 'fct'));

        if ($this->usuari->es_administrador) {
            $activerow->subtree[] = new tabobject('afegir_cicle', new moodle_url('/mod/fct/edit.php',
                                                array('cmid' => $id, 'page' => 'cicle')),
                                                get_string('afegeix_cicle_formatiu', 'fct'));
        }

        $row['cicle'] = $activerow;

        if ($type == 'edit' && !$this->id) {
            $tab['currentab'] = 'afegir_cicle';
        } else {
            $tab['currentab'] = 'ciclelist';
        }

        $tab['row'] = $row;

        return $tab;
    }

    public static function get_records($fctid, $userid = null, $searchparams = false, $pagenumber = false) {
        global $DB;

        $records = new stdClass;

        if ($fctid) {
            $records = $DB->get_records(static::$table, array('fct' => $fctid));
        }

        return $records;

    }

    public function view($id = false) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'cicles');
        if (!$id) {

            $fctid = $this->fct;
            if ($records = self::get_records($fctid)) {
                $table = $output->cicles_table($records);
                echo $table;
            } else {
                echo $output->notification(get_string('cap_cicle_formatiu', 'fct'));
            }
        }
    }

    public function delete() {
        global $DB;

        $records = $DB->count_records('fct_quadern', array('cicle' => $this->id));

        if ($records) {
            return false;
        }

        $DB->delete_records('fct_cicle', array('id' => $this->id));
        return true;

    }

    public function delete_message() {

        return get_string('segur_suprimir_cicle_formatiu', 'fct', $this->nom);
    }

    public function no_delete_message() {
        return get_string('cicle_formatiu_no_suprimible', 'fct', $this);
    }

    public static function validation($data) {
        global $DB;

        $errors = array();

        if ($data['id']) {
            return $errors;
        }
        if ($DB->record_exists('fct_cicle', array('nom' => trim($data['nom'])))) {
            $errors = array('nom' => 'nombre duplicado');
        }

        return $errors;

    }

    public function checkpermissions($type = 'view') {

        if (!$this->usuari->es_administrador && !$this->usuari->es_tutor_centre) {
            print_error('nopermissions', 'fct');
        }

        if ($type == 'edit') {
            if (!$this->id && !$this->usuari->es_administrador) {
                    print_error('nopermissions', 'fct');
            }
        }

        if ($type == 'delete' || $type == 'deletelink') {
            if (!$this->usuari->es_administrador && $type == 'delete') {
                    print_error('nopermissions', 'fct');
            } else if (!$this->usuari->es_administrador && $type == 'deletelink') {
                return false;
            }
        }

        return true;
    }

    public function prepare_form_data($data) {

    }

}