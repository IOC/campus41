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
 * Avisios FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_base.php');
require_once('fct_quadern_base.php');
require_once('fct_quadern_quinzena.php');

class fct_avisos extends fct_base{

    public $id;
    public $fct;
    public $quadern;
    public $data;
    public $tipus;
    public $quinzena_alumne;
    public $quinzena;
    public $objecte;

    protected static $table = 'fct_avis';
    protected $record_keys = array('id', 'quadern', 'data', 'objecte');
    protected $objecte_keys = array('id', 'quadern', 'data', 'tipus', 'quinzena_alumne', 'quinzena');

    protected $editform = 'fct_avis_edit_form.php';

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_general($id);

        $tab['currentab'] = 'avisos';
        $tab['inactivetabs'] = array();

        return $tab;
    }

    public function view($id = false, $index, $searchparams) {
        global $PAGE, $USER, $OUTPUT;

        if (!$id) {

            $output = $PAGE->get_renderer('mod_fct', 'avisos');

            if ($avisos = self::get_records($this->fct, $USER->id, false, $index)) {

                $baseurl = new moodle_url('/mod/fct/view.php', array('id' => $PAGE->cm->id, 'page' => 'avisos'));

                echo $OUTPUT->paging_bar($avisos['totalrecords'], $index, PAGENUMAVIS, $baseurl, 'index');
                $table = $output->avisos_table($avisos['records']);
                echo $table;
                echo $OUTPUT->paging_bar($avisos['totalrecords'], $index, PAGENUMAVIS, $baseurl, 'index');
            } else {
                echo $OUTPUT->notification(get_string('cap_avis', 'fct'));
            }

        }
    }

    public static function get_records($fctid, $userid = null, $searchparams = false, $index = null) {
        global $DB;

        $where = " FROM {fct_cicle} c"
                . " JOIN {fct_quadern} q ON q.cicle = c.id"
                . " JOIN {fct_avis} a ON a.quadern = q.id"
                . " WHERE c.fct = $fctid"
                . " AND q.tutor_centre = $userid"
                . " ORDER BY a.data DESC";

        $sql = "SELECT a.id, a.objecte" . $where;
        $countsql = "SELECT count(1)"  . $where;

        if ($records = $DB->get_records_sql($sql, null,  $index * PAGENUMAVIS, PAGENUMAVIS)) {
            $totalrecords = $DB->count_records_sql($countsql);

            $avisos = array();
            foreach ($records as $record) {
                $avisos[] = new fct_avisos($record);
            }
            return array('records' => $avisos, 'totalrecords' => $totalrecords);
        } else {
            return false;
        }
    }

    public function delete() {
        global $DB;

        $DB->delete_records('fct_avis', array('id' => $this->id));
        return true;
    }

    public function delete_message() {
        return get_string('segur_suprimir_avisos', 'fct');
    }

    public function quadern() {
        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        $quadern = new fct_quadern_base((int)$this->quadern);

        return $quadern;
    }

    public function titol_avis() {
        switch ($this->tipus) {
            case 'quinzena_afegida':
            case 'quinzena_alumne':
            case 'quinzena_empresa':
            case 'quinzena_tutor':
                $quinzena = new fct_quadern_quinzena((int)$this->quinzena);
                return get_string('avis_' . $this->tipus, 'fct', $quinzena->nom_periode($quinzena->periode) . " {$quinzena->any}");
            default:
                return get_string('avis_' . $this->tipus, 'fct');
        }
    }

    public static function registrar_avis($quadernid, $tipus, $quinzena=false) {
        global $DB;

        $records = $DB->get_records('fct_avis', array('quadern' => $quadernid), '',
                                              'id');

        foreach ($records as $record) {
                $avis = new fct_avisos((int)$record->id);
            if ($avis->tipus == $tipus and $avis->quinzena == $quinzena) {
                $avis->data = time();
                $avis->update();
                return;
            }
        }

        $data = new stdClass();
        $data->quadern = $quadernid;
        $data->data = time();
        $data->tipus = $tipus;
        $data->quinzena = $quinzena;
        $data->objecte = '';

        $avis = new fct_avisos();
        $avis->insert($data);

    }

    public function checkpermissions($type = 'view') {
        global $USER;

        if (!$this->usuari) {
            if (!isset($this->fct)) {
                print_error('nofct');
            }

            $this->usuari = new fct_usuari($this->fct, $USER->id);
        }

        if (!$this->usuari->es_administrador && !$this->usuari->es_tutor_centre) {
            print_error('nopermissions', 'fct');
        }
    }

    public function prepare_form_data($data) {
    }

}
