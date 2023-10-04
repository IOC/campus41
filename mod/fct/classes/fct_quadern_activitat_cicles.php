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
 * Quadern activitat cicles FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_activitat_cicles_edit_form.php');
require_once('fct_base.php');
require_once('fct_quadern_base.php');
require_once('fct_cicle.php');
require_once('fct_usuari.php');
require_once('fct_avisos.php');
require_once('fct_activitat.php');

class fct_quadern_activitat_cicles extends fct_base {

    public $id;
    public $fct;
    public $quadern;
    public $descripcio;
    public $nota;
    public $objecte;
    public $returnurl;

    protected static $table = 'fct_activitat';
    protected $record_keys = array('id', 'quadern', 'objecte');
    protected $objecte_keys = array('id', 'quadern', 'descripcio', 'nota');
    protected $editform = 'fct_quadern_activitat_cicles_edit_form';

    public function __construct($record) {
        if (isset($record)) {
            parent::__construct($record);
        }

        if (isset($this->fct)) {
            $cm = get_coursemodule_from_instance('fct', $this->fct);
            $this->returnurl = new moodle_url('view.php', array('id' => $cm->id,
                                              'page' => 'quadern_activitat',
                                              'quadern' => $this->quadern));
        }
    }

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->quadern);

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

        $row = $tab['row'];
        $row['quadern_activitat']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = 'afegeix_activitats_cicle';
        $tab['row'] = $row;

        return $tab;
    }

    public function get_cicle_activitats($quadern) {
        global $DB;

        if ($record = $DB->get_record('fct_quadern', array('id' => $quadern), 'cicle')) {
                $cicle = new fct_cicle((int)$record->cicle);

                return $cicle->activitats;
        } else {
            return false;
        }
    }

    public function set_data($data) {
    }

    public function insert($data) {
        global $USER;

        if (!isset($data->quadern)) {
            print_error('noquadern');
        }

        $datakeys = array_keys((array)$data);

        $pregmatchexp = '"'.'/^'.'activity'.'_/'.'"';

        $arrayfiltered = array_filter($datakeys, create_function('$a', 'return preg_match('.$pregmatchexp.', $a);'));

        $activitieskeys = array_map(create_function('$a', 'return preg_replace('.$pregmatchexp.', '."''".', $a);'), $arrayfiltered);
        $activitieskeys = array_flip($activitieskeys);

        if ($activitascicle = $this->get_cicle_activitats($data->quadern)) {
            $activitats = array_intersect_key($activitascicle, $activitieskeys);
            if (!empty($activitats)) {
                foreach ($activitats as $activitat) {
                    $data->descripcio = $activitat;
                    parent::insert($data);
                }
            }
             $quadern = new fct_quadern_base($data->quadern);

            if ($quadern->tutor_empresa == $USER->id) {
                fct_avisos::registrar_avis($this->quadern, 'pla_activitats');
            }
        } else {
            return;
        }

    }

    public function prepare_form_data($data) {

        if (!isset($data->quadern)) {
            print_error('noquadern');
        }

        $data->activitatscicle = $this->get_cicle_activitats($data->quadern);
    }

    public function checkpermissions($type = 'view') {
        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        if ($this->usuari->es_administrador) {
            return true;
        }

        $quadern = new fct_quadern_base((int)$this->quadern);

        if (($this->usuari->es_alumne && ($this->usuari->id != $quadern->alumne)) ||
           ($this->usuari->es_tutor_centre && ($this->usuari->id != $quadern->tutor_centre)) ||
           ($this->usuari->es_tutor_empresa && ($this->usuari->id != $quadern->tutor_empresa))) {
                print_error('nopermissions', 'fct');
        }
    }

    public static function validation($data) {
    }

}
