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
 * Quadern quinzena FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_quinzena_edit_form.php');
require_once('fct_base.php');
require_once('fct_cicle.php');
require_once('fct_quadern_base.php');
require_once('fct_quadern_activitat.php');
require_once('fct_frases_retroaccio.php');
require_once('fct_usuari.php');
require_once('fct_data.php');
require_once('fct_avisos.php');

class fct_quadern_quinzena extends fct_base {

    public $id;
    public $fct;
    public $quadern;
    public $_any_;
    public $any;
    public $periode;
    public $objecte = true;
    public $dies;
    public $activitats;
    public $valoracions;
    public $hores;
    public $observacions_alumne;
    public $observacions_centre;
    public $observacions_empresa;
    public $resum;


    protected static $table = 'fct_quinzena';
    protected $record_keys = array('id', 'quadern', 'any_', 'periode', 'objecte');
    protected $objecte_keys = array(
        'id',
        'quadern',
        'any',
        'periode',
        'hores',
        'dies',
        'activitats',
        'valoracions',
        'observacions_alumne',
        'observacions_centre',
        'observacions_empresa',
    );
    protected $editform = 'fct_quadern_quinzena_edit_form';

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->quadern);

        $subtree = array();

        $subtree[] = new tabobject('quinzenesllist', new moodle_url('/mod/fct/view.php',
                                        array('id' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_quinzena')),
                                        get_string('quinzenes', 'fct'));

        if (self::checkpermissions('addlink')) {
            $subtree[] = new tabobject('afegeix_quinzena', new moodle_url('/mod/fct/edit.php',
                                            array('cmid' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_quinzena')),
                                            get_string('afegeix_quinzena', 'fct'));
        }

        $subtree[] = new tabobject('resum_seguiment', new moodle_url('/mod/fct/view.php',
                                        array('id' => $id, 'quadern' => $this->quadern, 'page' => 'resum_seguiment')),
                                        get_string('resum_seguiment', 'fct'));

        $row = $tab['row'];
        $row['quadern_quinzena']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = $type == 'view' ? 'quinzenesllist' : 'afegeix_quinzena';
        $tab['row'] = $row;

        return $tab;
    }


    public function __construct($record = null) {
        global $DB, $USER;

        parent::__construct($record);

        if (empty($this->fct)) {
            if ($cicle = $DB->get_record('fct_quadern', array('id' => $this->quadern), 'cicle')) {
                if ($fctrecord = $DB->get_record('fct_cicle', array('id' => $cicle->cicle), 'fct')) {
                    $this->fct = $fctrecord->fct;
                } else {
                    print_error('nofct');
                }
            }
        }

        if (!isset($this->usuari) and $this->fct) {
            $userid = $USER->id;
            $this->usuari = new fct_usuari($this->fct, $userid);
        }
    }

    public function view($id = false) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quinzena');

        if (!$id) {
            $quadern = new fct_quadern_base((int)$this->quadern);
            if ($quinzenes = self::get_records($this->quadern)) {
                $table = $output->quinzenes_table($quinzenes);
                echo $table;
            } else {
                echo $output->notification(get_string('cap_quinzena', 'fct'));
            }
            if ($dataprevista = $this->data_prevista_valoracio_parcial($quadern)) {
                echo $output->data_prevista($dataprevista);
            }
        } else {
            $quinzena = new fct_quadern_quinzena($id);
            echo $output->quinzena_detail($quinzena);
        }
    }

    public static function get_records($quadern = false, $userid = false, $searchparams = false, $pagenumber = false) {
        global $DB;

        $records = new stdClass;

        $params = array();
        if ($quadern) {
            $params = array('quadern' => $quadern);
        }

        $records = $DB->get_records('fct_quinzena', $params);

        $quinzenes = array();
        foreach ($records as $record) {
            $quinzenes[] = new fct_quadern_quinzena($record);
        }

        return $quinzenes;

    }

    public function insert($data) {
        global $DB, $USER;

        if (isset($data->any)) {
            $data->any_ = $data->any;
        }

        parent::insert($data);

        $quadern = new fct_quadern_base($this->quadern);

        if ($quadern->alumne == $USER->id) {
            fct_avisos::registrar_avis($this->quadern, 'quinzena_afegida', $this->id);
        }

    }

    public function update() {
        global $USER;

        parent::update();

        $quadern = new fct_quadern_base($this->quadern);

        if ($quadern->alumne == $USER->id) {
            fct_avisos::registrar_avis($this->quadern, 'quinzena_alumne', $this->id);
        }

        if ($quadern->tutor_empresa == $USER->id) {
            fct_avisos::registrar_avis($this->quadern, 'quinzena_empresa', $this->id);
        }
    }

    public function delete() {
        global $DB, $USER;

        if (!isset($this->id)) {
            print_error('noidgiven');
        }

        $quadern = new fct_quadern_base((int)$this->quadern);

        if ($quadern->alumne == $USER->id) {
            fct_avisos::registrar_avis($this->quadern, 'quinzena_suprimida', $this->id);
        }

        $DB->delete_records('fct_quinzena', array('id' => $this->id));

        return true;
    }

    public function delete_message() {
        return get_string('segur_suprimir_quinzena', 'fct', $this->nom_periode($this->periode). ' '. $this->any);
    }

    public function prepare_form_data($data) {
        if (!isset($data->quadern)) {
            print_error('noquadern');
        }

        $quadern = new fct_quadern_base($data->quadern);
        $data->anyselect = $quadern->opcions_any();
        $data->periodeselect = $this->opcions_periode();
        $data->activitatscicle = array();

        if ($activitats = fct_quadern_activitat::get_records($data->quadern)) {
            foreach ($activitats as $activitat) {
                $data->activitatscicle[$activitat->id] = $activitat->descripcio;
            }
        }

        $frases = new fct_frases_retroaccio((int)$this->fct);

        $data->frases_centre = $frases->frases_centre;
        $data->frases_empresa = $frases->frases_empresa;
        $data->usuari = $this->usuari;
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

    public function summary() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quinzena');

        $resum = array();
        $quinzenes = self::get_records($this->quadern);

        if ($quinzenes) {
            foreach ($quinzenes as $quinzena) {
                $any = $quinzena->any;
                $periode = $quinzena->periode;
                $mes = (int) ($periode / 2);
                $trimestre = (int) ($mes / 3);

                if (!isset($resum[$any])) {
                    $resum[$any] = array();
                }

                if (!isset($resum[$any][$trimestre])) {
                    $resum[$any][$trimestre] = array();
                }

                if (!isset($resum[$any][$trimestre][$mes])) {
                    $resum[$any][$trimestre][$mes] = (object) array(
                        'dies' => 0, 'hores' => 0);
                }

                $resum[$any][$trimestre][$mes]->dies += count($quinzena->dies);
                $resum[$any][$trimestre][$mes]->hores += $quinzena->hores;
                $this->total_hores += $quinzena->hores;
                $this->total_dies += count($quinzena->dies);
            }

            $this->resum = $resum;

            foreach ($this->resum as $any => $resum_any) {
                foreach ($resum_any as $trimestre => $resum_trimestre) {
                    $lines = array();
                    $lines[] = $this->mostrar_resum_trimestre($any, $trimestre);

                    // $output->resum_table($trimestre, $lines);
                }

            }
        }
    }

    public function mostrar_resum_trimestre($any, $trimestre) {
        $dies = 0;
        $hores = 0;
        for ($mes = $trimestre * 3; $mes < $trimestre * 3 + 3; $mes++) {
            if (isset($this->resum[$any][$trimestre][$mes])) {
                $record = $this->resum[$any][$trimestre][$mes];
                // $taula->add_data(array(self::nom_mes($mes), $record->dies, $record->hores));
                $dies += $record->dies;
                $hores += $record->hores;
                return array($mes, $record->dies, $record->hores);
            }
        }
    }

    protected function opcions_periode() {
        $opcions = array();

        for ($periode = 0; $periode <= 23; $periode++) {
            $opcions[$periode] = $this->nom_periode($periode);
        }
        return $opcions;
    }

    public function nom_periode($periode, $any=2001) {
        $mes = floor((int) $periode / 2);
        $dies = ($periode % 2 == 0) ? '1-15' :
            '16-' . cal_days_in_month(CAL_GREGORIAN, $mes + 1, $any);
        return $dies . ' ' . $this->nom_mes($mes);
    }

    protected function nom_mes($mes) {
        $time = mktime(0, 0, 0, $mes + 1, 1, 2000);
        return strftime('%B', $time);
    }

    public function checkpermissions($type = 'view') {

        if ($this->usuari->es_administrador) {
            return true;
        }
        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        $quadern = new fct_quadern_base((int)$this->quadern);

        if ($type === 'add' || $type === 'addlink') {
            if ($quadern->estat == 'tancat' || $quadern->estat == 'proposat' || $this->usuari->es_tutor_empresa) {
                if ($type === 'addlink') {
                    return false;
                }
                print_error('nopermissions', 'fct');
            }
        } else if ($type === 'edit' || $type === 'editlink') {
            if ($quadern->estat == 'tancat' || $quadern->estat == 'proposat') {
                if ($type === 'editlink') {
                    return false;
                }
                print_error('nopermissions', 'fct');
            }
        } else if ($type === 'delete' || $type === 'deletelink') {
            if ($quadern->estat == 'tancat' || $quadern->estat == 'proposat' || $this->usuari->es_tutor_empresa) {
                if ($type === 'deletelink') {
                    return false;
                }
                print_error('nopermissions', 'fct');
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

    public static function maxim_hores_quinzena($quadernid, $any, $periode, $dies) {
        $hores = 0.0;

        foreach ($dies as $dia) {
            $data = new fct_data($dia, floor($periode / 2) + 1, $any);
            $quadern = new fct_quadern_base((int)$quadernid);

            if ($conveni = $quadern->conveni_data($data)) {
                $hores += $conveni->hores_dia($data->dia_setmana());
            }
        }

        return $hores;
    }

    public static function validation($data) {
        $dies = array();

        if (isset($data['dies'])) {
            $dies = explode(',', $data['dies']);
        }

        $max_hores = self::maxim_hores_quinzena($data['quadern'], $data['any'], $data['periode'], $dies);

        $hores = (float) $data['grouphores']['hores'];
        if (!empty($data['grouphores']['minuts'])) {
            $hores += $data['grouphores']['minuts'] / 60;
        }

        if ($hores > $max_hores) {
            return array('grouphores' => get_string('hores_superior_horari', 'fct'));
        }
    }

    private function data_prevista_valoracio_parcial($quadern) {
        if ($conveni = $quadern->ultim_conveni()) {
            $inici = DateTime::createFromFormat('U', $conveni->data_inici);
            $final = DateTime::createFromFormat('U', $conveni->data_final);
            $dies = (int) ($inici->diff($final)->format('%a') / 2);
            $interval = new DateInterval("P{$dies}D");
            return $inici->add($interval)->getTimestamp();
        }
        return false;
    }
}