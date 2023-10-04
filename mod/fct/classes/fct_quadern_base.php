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
 * Quadern base FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_base.php');
require_once('fct_quadern_quinzena.php');
require_once('fct_cicle.php');
require_once('fct_conveni.php');
require_once('fct_usuari.php');

class fct_quadern_base extends fct_base {

    protected static $table = 'fct_quadern';

    public $id;
    public $fct;
    public $quadern;
    public $alumne;
    public $objecte;
    public $nom_empresa;
    public $cicle;
    public $tutor_centre;
    public $tutor_empresa;
    public $estat;
    public $empresa;
    public $dades_alumne;
    public $convenis;
    public $hores_credit = 0;
    public $hores_practiques = 0;
    public $hores_anteriors = 0;
    public $exempcio = 0;
    public $prorrogues = '';
    public $valoracio_parcial = array();
    public $valoracio_final = array();
    public $valoracio_resultats = array();
    public $qualificacio;
    public $qualificacio_global;
    public $data_final;

    public $inssimage;

    protected static $dataobject;

    protected $editform = 'fct_quadern_alumne_edit_form';

    protected $objecte_keys = array('id',
                                    'cicle',
                                    'alumne',
                                    'tutor_centre',
                                    'tutor_empresa',
                                    'estat',
                                    'dades_alumne',
                                    'empresa',
                                    'convenis',
                                    'hores_credit',
                                    'hores_practiques',
                                    'hores_anteriors',
                                    'exempcio',
                                    'prorrogues',
                                    'valoracio_parcial',
                                    'valoracio_final',
                                    'valoracio_resultats',
                                    'qualificacio',
                                    'qualificacio_global'
                                    );

    protected $record_keys = array('id',
                                    'alumne',
                                    'tutor_centre',
                                    'tutor_empresa',
                                    'nom_empresa',
                                    'cicle',
                                    'estat',
                                    'data_final',
                                    'objecte');

    public static $estats = array(
                             OBERT => 'Obert',
                             TANCAT => 'Tancat',
                             PROPOSAT => 'Proposat');

    protected static $dataobjectkeys = array();

    public function __construct($record = null) {
        global $DB, $USER;

        if (isset($record->fct)) {
            $this->fct = $record->fct;
        }

        if (isset($record->quadern)) {
            parent::__construct((int)$record->quadern);
        } else {
            parent::__construct($record);
        }

        if (!isset($this->fct)) {
            if ($record = $DB->get_record('fct_cicle', array('id' => $this->cicle), 'fct')) {
                $this->fct = $record->fct;
                if ($USER->id) {
                    $this->usuari = new fct_usuari($this->fct, $USER->id);
                }
            } else {
                print_error('nofct');
            }
        }

        if (isset($this->convenis)) {
            $convenisobject = new stdClass;
            foreach ($this->convenis as $conveni) {
                $uuid = $conveni->uuid;
                $convenisobject->$uuid = new fct_conveni($conveni);
            }
            $this->convenis = $convenisobject;
        }

    }

    public function __get($name) {

        $dataobjectkeys = static::$dataobjectkeys;
        $dataobject = static::$dataobject;

        if (!isset($this->$dataobject)) {
            return false;
        }

        if (array_key_exists($name, array_flip($dataobjectkeys))) {
             return (isset($this->$dataobject->$name) ? $this->$dataobject->$name : '');
        }
        return false;
    }

    public function insert($data) {
        $this->create_empresa($data);
        $this->create_alumne();
        $this->create_qualificacions();

        parent::insert($data);
    }

    public function set_data($data) {

        if ($dataobject = static::$dataobject) {
            $this->$dataobject = (object)array_intersect_key((array)$data, array_flip(static::$dataobjectkeys));
        }

        parent::set_data($data);
    }

    public function prepare_form_data($data) {

        $dataobject = static::$dataobject;

        if (isset($data->$dataobject)) {
            $formdata = (array)$data->$dataobject;

            foreach ($formdata as $key => $value) {
                $data->$key = $value;
            }
        }

    }

    public function data_inici() {
        $data = false;
        if (isset($this->convenis)) {
            foreach ($this->convenis as $conveni) {
                if (!$data or $conveni->data_inici < $data) {
                    $data = $conveni->data_inici;
                }
            }
        }
        return $data;
    }

    public function data_final() {
        $data = false;
        if (isset($this->convenis)) {
            foreach ($this->convenis as $conveni) {
                if (!$data or $conveni->data_final > $data) {
                    $data = $conveni->data_final;
                }
            }
        }
        return $data;
    }

    public function opcions_any() {

        $opcions = array();
        $any_min = date('Y', $this->data_inici());
        $any_max = date('Y', $this->data_final());
        for ($any = $any_min; $any <= $any_max; $any++) {
            $opcions[$any] = "$any";
        }
        return $opcions;
    }

    public function opcions_periode() {
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

    public function nom_mes($mes) {
        $time = mktime(0, 0, 0, $mes + 1, 1, 2000);
        return strftime('%B', $time);
    }

    public function get_frases_cicle() {
        $cicle = new fct_cicle((int)$this->cicle);
        return $cicle->activitats;
    }

    public function hores_realitzades_quadern($quadernid) {
        $hores = 0;
        $quinzenes = fct_quadern_quinzena::get_records($quadernid);
        foreach ($quinzenes as $quinzena) {
            $hores += $quinzena->hores;
        }
        return $hores;
    }

    public function checkpermissions($type = 'view') {

        if ($this->usuari->es_administrador) {
            return true;
        }

        if ($type == 'edit' || $type == 'editlink' || $type == 'edit_company_name') {
            if ($type == 'edit_company_name') {
                return ($this->usuari->es_administrador ||
                    ($this->usuari->es_tutor_centre && ($this->estat == 'proposat' || $this->estat == 'obert')));
            } else if (($this->estat == 'tancat' && !$this->usuari->es_administrador) || $this->usuari->es_tutor_empresa) {
                if ($type == 'editlink') {
                    return false;
                } else {
                    print_error('nopermissions', 'fct');
                }
            } else {
                return true;
            }
        }

        if ($type == 'image') {
            return ($this->usuari->es_administrador ||
               ($this->usuari->es_alumne && ($this->usuari->id == $this->alumne)) ||
               ($this->usuari->es_tutor_centre && ($this->usuari->id == $this->tutor_centre)) ||
               ($this->usuari->es_tutor_empresa && ($this->usuari->id == $this->tutor_empresa)));
        }

        if (($this->usuari->es_alumne && ($this->usuari->id != $this->alumne)) ||
           ($this->usuari->es_tutor_centre && ($this->usuari->id != $this->tutor_centre)) ||
           ($this->usuari->es_tutor_empresa && ($this->usuari->id != $this->tutor_empresa))) {
                print_error('nopermissions', 'fct');
        }

        return true;
    }

    public function conveni_data($data) {
        foreach ($this->convenis as $conveni) {
            if ($data->en_periode(fct_data::time($conveni->data_inici),
                                  fct_data::time($conveni->data_final))) {
                return $conveni;
            }
        }
    }

    public function es_alumne() {
        return $this->usuari->id == $this->alumne;
    }

    public function es_tutor_centre() {
        if (isset($this->tutor_centre)) {
            return $this->usuari->id == $this->tutor_centre;
        }
        return false;
    }

    public function es_tutor_empresa() {
        if (isset($this->tutor_empresa)) {
            return $this->usuari->id == $this->tutor_empresa;
        }
    }

    protected function subtree($id, $quadern) {

        $subtree = array();

        $params = array(
            'id' => $id,
            'quadern' => $quadern,
            'page' => 'quadern_dades',
        );
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_centre', $url, get_string('centre_docent', 'fct'));

        $params['subpage'] = 'quadern_alumne';
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_alumne', $url, get_string('alumne', 'fct'));

        $params['subpage'] = 'quadern_empresa';
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_empresa', $url, get_string('empresa', 'fct'));

        $params['subpage'] = 'quadern_conveni';
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_conveni', $url, get_string('conveni', 'fct'));

        $params['subpage'] = 'quadern_horari';
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_horari', $url, get_string('horari_practiques', 'fct'));

        $params['subpage'] = 'quadern_dades_relatives';
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_dades_relatives', $url, get_string('dades_relatives', 'fct'));

        $params = array(
            'id' => $id,
            'quadern' => $quadern,
            'page' => 'quadern_main',
            'action' => 'export_html',
        );
        $url = new moodle_url('/mod/fct/view.php', $params);
        $subtree[] = new tabobject('quadern_to_html', $url, get_string('exporta_html', 'fct'));

        return $subtree;
    }

    public function ultim_conveni() {
        return isset($this->convenis) ? end($this->convenis) : false;
    }

    public function nom_cicle() {
        global $DB;
        if ($cicle = $DB->get_field('fct_cicle', 'nom', array('id' => $this->cicle))) {
            return $cicle;
        }
        return '';
    }

    private function create_empresa($data) {
        if (!$this->empresa) {
            $empresa = new stdClass;
            $empresa->nom = isset($data->nom_empresa) ? $data->nom_empresa : '';
            $empresa->adreca = '';
            $empresa->poblacio = '';
            $empresa->codi_postal = '';
            $empresa->telefon = '';
            $empresa->fax = '';
            $empresa->email = '';
            $empresa->nif = '';
            $empresa->codi_agrupacio = '';
            $empresa->sic = '';
            $empresa->nom_responsable = '';
            $empresa->cognoms_responsable = '';
            $empresa->dni_responsable = '';
            $empresa->carrec_responsable = '';
            $empresa->nom_tutor = '';
            $empresa->cognoms_tutor = '';
            $empresa->dni_tutor = '';
            $empresa->email_tutor = '';
            $empresa->nom_lloc_practiques = '';
            $empresa->adreca_lloc_practiques = '';
            $empresa->poblacio_lloc_practiques = '';
            $empresa->codi_postal_lloc_practiques = '';
            $empresa->telefon_lloc_practiques = '';

            $this->empresa = $empresa;
        }
    }

    private function create_alumne() {

        $alumne = new stdClass;
        $alumne->dni = '';
        $alumne->data_naixement = '';
        $alumne->adreca = '';
        $alumne->poblacio = '';
        $alumne->codi_postal = '';
        $alumne->telefon = '';
        $alumne->email = '';
        $alumne->procedencia = '';
        $alumne->inss = '';
        $alumne->targeta_sanitaria = '';

        $this->dades_alumne = $alumne;
    }

    private function create_qualificacions() {

        $qualificacio = new stdClass;
        $qualificacio->apte = 0;
        $qualificacio->nota = 0;
        $qualificacio->data = 0;
        $qualificacio->observacions = '';

        $this->qualificacio = $qualificacio;
        $this->qualificacio_global = $qualificacio;
    }
}