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
 * Quadern valoracio activitat FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('form/quadern_valoracio_activitat_edit_form.php');
require_once('fct_base.php');
require_once('fct_cicle.php');
require_once('fct_quadern_activitat.php');
require_once('fct_quadern.php');

class fct_quadern_valoracio_activitat extends fct_base {

    public $id;
    public $fct;
    public $quadern;
    public $descripcio;
    public $nota;
    public $objecte;

    private $activitats = array();


    protected static $table = 'fct_activitat';
    protected $record_keys = array('id', 'quadern', 'objecte');
    protected $objecte_keys = array('id', 'quadern', 'descripcio', 'nota');
    protected $editform = 'fct_quadern_valoracio_activitat_edit_form';

    public function __construct($record = null) {
        global $DB;

        parent::__construct($record);

        if (empty($this->fct) and is_numeric($record)) {
            $this->quadern = $record;
            if ($cicle = $DB->get_record('fct_quadern', array('id' => $this->quadern), 'cicle')) {
                if ($fctrecord = $DB->get_record('fct_cicle', array('id' => $cicle->cicle), 'fct')) {
                    $this->fct = $fctrecord->fct;
                } else {
                    print_error('nofct');
                }
            }
        }
    }

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->quadern);

        $subtree = array();

        $params = array(
            'id' => $id,
            'quadern' => $this->quadern,
            'page' => 'quadern_valoracio',
            'valoracio' => 'parcial',
        );

        $subtree[] = new tabobject('valoracio_parcial_actituds',
                              new moodle_url('view.php', $params),
                              get_string('valoracio_parcial_actituds', 'fct'));

        $params['valoracio'] = 'final';
        $subtree[] = new tabobject('valoracio_final_actituds',
                              new moodle_url('view.php', $params),
                              get_string('valoracio_final_actituds', 'fct'));

        $params['valoracio'] = 'resultats';
        $subtree[] = new tabobject('valoracio_resultats',
                              new moodle_url('view.php', $params),
                              get_string('valoracio_resultats', 'fct'));

        $params = array(
            'id' => $id,
            'quadern' => $this->quadern,
            'page' => 'quadern_valoracio',
            'subpage' => 'quadern_valoracio_activitat',
        );
        $subtree[] = new tabobject('valoracio_activitats',
                              new moodle_url('view.php', $params),
                              get_string('valoracio_activitats', 'fct'));

        $params['subpage'] = 'quadern_qualificacio';
        $subtree[] = new tabobject('qualificacio_quadern',
                              new moodle_url('view.php', $params),
                              get_string('qualificacio_quadern', 'fct'));

        $row = $tab['row'];
        $row['quadern_valoracio']->subtree = $subtree;
        $tab['currentab'] = 'valoracio_activitats';
        $tab['row'] = $row;

        return $tab;
    }


    public function view($id = false) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quadern_valoracio_activitat');

        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        if ($records = self::get_records($this->quadern)) {
            $output->view($records, $this->quadern);
            return;
        }
        echo $output->notification(get_string('cap_activitat', 'fct'));
        return;
    }

    public static function get_records($quadern, $usuari = false, $searchparams = false, $pagenumber = false) {
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


    public function prepare_form_data($data) {
        global $DB;

        if (!isset($data->quadern)) {
            print_error('noquadern');
        }

        $activitats = $this->get_records($data->quadern);
        $data->activitats = $activitats;
        foreach ($activitats as $activitat) {
            $notakey = 'nota_'.$activitat->id;
            $data->$notakey = $activitat->nota;
        }
        $data->barem = $this->barem_valoracio();

    }

    public function insert($data) {
        $activitats = self::get_records($data->quadern);

        foreach ($activitats as $activitat) {
            $notakey = 'nota_'.$activitat->id;
            $activitat->nota = $data->$notakey;
            $activitat->create_object();
            $activitat->update();
        }

    }

    public function set_data($data) {
        $activitats = self::get_records($data->quadern);

        foreach ($activitats as $activitat) {
            $notakey = 'nota_'.$activitat->id;
            $activitat->nota = $data->$notakey;
            $activitat->create_object();
            $this->activitats[] = $activitat;
        }
    }

    public function update() {
        foreach ($this->activitats as $activitat) {
            $activitat->update();
        }
    }

    public static function validation($data) {

    }

    public function checkpermissions($type = 'view') {
        $quadern = new fct_quadern($this->quadern);

        if ($quadern->usuari->es_administrador) {
            return true;
        }

        if ($type === 'edit' || $type === 'editlink') {
            if ($quadern->usuari->es_administrador or
                ($quadern->estat == 'obert' and ($quadern->usuari->es_tutor_centre or $quadern->usuari->es_tutor_empresa))) {
                    return true;
            } else if ($type === 'editlink') {
                return false;
            } else {
                print_error('nopermissions', 'fct');
            }
        }
    }

    public function barem_valoracio() {
        return array(
            0 => '-',
            1 => get_string('barem_a', 'fct'),
            2 => get_string('barem_b', 'fct'),
            3 => get_string('barem_c', 'fct'),
            4 => get_string('barem_d', 'fct'),
            5 => get_string('barem_e', 'fct'),
        );
    }


}