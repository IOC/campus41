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
 * Quadern activitat FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_activitat_edit_form.php');
require_once('fct_base.php');
require_once('fct_quadern_base.php');
require_once('fct_cicle.php');
require_once('fct_usuari.php');

class fct_quadern_activitat extends fct_base {

    public $id;
    public $fct;
    public $quadern;
    public $descripcio;
    public $nota;
    public $objecte = true;

    protected static $table = 'fct_activitat';
    protected $record_keys = array('id', 'quadern', 'objecte');
    protected $objecte_keys = array('id', 'quadern', 'descripcio', 'nota');
    protected $editform = 'fct_quadern_activitat_edit_form';

    public function __construct($record) {
        global $DB, $USER;

        parent::__construct($record);

        if (!isset($this->fct)) {
            if ($record = $DB->get_record('fct_quadern', array('id' => $this->quadern))) {
                if ($record = $DB->get_record('fct_cicle', array('id' => $record->cicle))) {
                    $this->fct = $record->fct;
                    $this->usuari = new fct_usuari($this->fct, $USER->id);
                }
            } else {
                print_error('nofct');
            }
        }
    }

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->quadern);

        if ($this->permis_editar()) {
            $row = $tab['row'];
            $subtree = array();

            $subtree[] = new tabobject('activitatllist', new moodle_url('/mod/fct/view.php',
                                            array('id' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_activitat')),
                                            get_string('activitat', 'fct'));

            $subtree[] = new tabobject('afegeix_activitat', new moodle_url('/mod/fct/edit.php',
                                            array('cmid' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_activitat')),
                                            get_string('afegeix_activitat', 'fct'));

            $subtree[] = new tabobject('afegeix_activitats_cicle', new moodle_url('/mod/fct/edit.php',
                                            array('cmid' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_activitat', 'subpage' => 'quadern_activitat_cicles')),
                                            get_string('afegeix_activitats_cicle', 'fct'));

            $subtree[] = new tabobject('suprimeix_activitats', new moodle_url('/mod/fct/edit.php',
                                            array('cmid' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_activitat', 'delete' => true, 'deleteall' => true)),
                                            get_string('suprimeix_activitats', 'fct'));

            $row['quadern_activitat']->subtree = $subtree;
            $tab['row'] = $row;
            $tab['currentab'] = $type == 'view' ? 'activitatllist' : 'afegeix_activitat';
        } else {
            $tab['currentab'] = 'quadern_activitat';
        }

        return $tab;
    }

    public function view($id = false) {
        global $PAGE;

        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        $output = $PAGE->get_renderer('mod_fct', 'quadern_activitat');

        if ($records = self::get_records($this->quadern)) {
            $table = $output->activitats_table($records);
            echo $table;
        } else {
            echo $output->notification(get_string('cap_activitat', 'fct'));
        }
    }

    public function insert($data) {
        global $DB, $USER;

        $quadern = new fct_quadern_base($this->quadern);

        if ($quadern->tutor_empresa == $USER->id) {
            fct_avisos::registrar_avis($this->quadern, 'pla_activitats');
        }
        parent::insert($data);
    }

    public static function get_records($quadern, $userid = false, $searchparams = false, $pagenumber = false) {
        global $DB;

        if (!$activitatsrecords = $DB->get_records('fct_activitat', array('quadern' => $quadern))) {
            return false;
        }

        $activitats = array();

        foreach ($activitatsrecords as $activitat) {
            $activitats[] = new fct_quadern_activitat((int)$activitat->id);
        }
        return $activitats;
    }

    public function delete() {
        global $DB;

        $DB->delete_records('fct_activitat', array('id' => $this->id));
        return true;
    }

    public function delete_message($deleteall = false) {
        return $deleteall ? get_string('segur_suprimir_activitats', 'fct') : get_string('segur_suprimir_activitat', 'fct');
    }

    public function checkpermissions($type = 'view') {
        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        if ($this->usuari->es_administrador) {
            return true;
        }

        $quadern = new fct_quadern_base((int)$this->quadern);

        if ($type == 'edit' || $type = 'editlink') {
            if (($this->usuari->es_alumne) || ($quadern->estat == 'tancat' && !$this->usuari->es_administrador)) {
                if ($type == 'editlink') {
                    return false;
                } else {
                    print_error('nopermissions', 'fct');
                }
            }
        } else {
            if (($this->usuari->es_alumne && ($this->usuari->id != $quadern->alumne)) ||
               ($this->usuari->es_tutor_centre && ($this->usuari->id != $quadern->tutor_centre)) ||
               ($this->usuari->es_tutor_empresa && ($this->usuari->id != $quadern->tutor_empresa))) {
                    print_error('nopermissions', 'fct');
            }
        }
        return true;
    }

    private function permis_editar() {
        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        $record = new stdClass;
        $record->quadern = $this->quadern;
        $record->fct  = $this->fct;

        $quadern = new fct_quadern_base($record);

        $permiseditar = ($this->usuari->es_administrador or
                                (in_array($quadern->estat, array('proposat', 'obert')) and
                                 ($quadern->es_tutor_centre() or $quadern->es_tutor_empresa())));

        return $permiseditar;
    }


    public function prepare_form_data($data) {
    }

    public static function validation($data) {
    }


}