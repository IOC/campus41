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
 * Quader valoracio FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_valoracio_edit_form.php');
require_once('fct_quadern_base.php');
require_once('fct_base.php');
require_once('fct_cicle.php');


class fct_quadern_valoracio extends fct_quadern_base {

    protected static $dataobject = '';
    public $valoracio;
    public $editform = 'fct_quadern_valoracio_edit_form';

    public $valoraciollist;

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);

        $subtree = array();

        $subtree[] = new tabobject('valoracio_parcial_actituds',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $this->id, 'page' => 'quadern_valoracio', 'valoracio' => 'parcial')),
                              get_string('valoracio_parcial_actituds', 'fct'));

        $subtree[] = new tabobject('valoracio_final_actituds',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $this->id, 'page' => 'quadern_valoracio', 'valoracio' => 'final')),
                              get_string('valoracio_final_actituds', 'fct'));

        $subtree[] = new tabobject('valoracio_resultats',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $this->id, 'page' => 'quadern_valoracio', 'valoracio' => 'resultats')),
                              get_string('valoracio_resultats', 'fct'));

        $subtree[] = new tabobject('valoracio_activitats',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $this->id, 'page' => 'quadern_valoracio', 'subpage' => 'quadern_valoracio_activitat')),
                              get_string('valoracio_activitats', 'fct'));

        $subtree[] = new tabobject('qualificacio_quadern',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $this->id, 'page' => 'quadern_valoracio', 'subpage' => 'quadern_qualificacio')),
                              get_string('qualificacio_quadern', 'fct'));

        $row = $tab['row'];
        $row['quadern_valoracio']->subtree = $subtree;
        $tab['currentab'] = ($this->valoracio == 'parcial') ? 'valoracio_parcial_actituds' : ($this->valoracio == 'final' ? 'valoracio_final_actituds' : 'valoracio_resultats');
        $tab['row'] = $row;

        return $tab;
    }

    public function __construct($record) {
        parent::__construct($record);
        $this->valoracio = isset($record->valoracio) ? $record->valoracio : '';
    }

    public function view() {
        global $PAGE;

        parent::__construct((int)$this->id);

        $output = $PAGE->get_renderer('mod_fct',
            'quadern_valoracio');

        $this->create_llist();

        echo $output->view($this);

        return true;

    }

    public function prepare_form_data($data) {
        $data->barem = $this->barem_valoracio();
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

    public function actituds_llist() {

        $elements = array();
        for ($i = 0; $i < 15; $i++) {
            $elements[$i] = get_string('actitud_' . ($i + 1), 'fct');
        }

        return $elements;
    }

    public function resultats_llist() {

        $elements = array();
        foreach (array(1 => 11, 2 => 11, 3 => 8) as $seccio => $n) {
            $elements["$seccio"] = get_string("resultat_aprenentatge_{$seccio}", 'fct');
            for ($i = 1; $i <= $n; $i++) {
                $elements["$seccio.$i"] = get_string("resultat_aprenentatge_{$seccio}.{$i}", 'fct');
            }
        }
        return $elements;
    }

    public function create_llist() {
        switch ($this->valoracio) {
            case 'parcial':
                $this->valoraciollist = (array)$this->actituds_llist();
                break;

            case 'final':
                $this->valoraciollist = (array)$this->actituds_llist();
                break;

            case 'resultats':
                $this->valoraciollist = (array)$this->resultats_llist();
                break;

            case 'activitats':
                $this->valoraciollist = (array)$this->actituds_llist();
                break;

            default:
                 print_error('novalidvaloracio');
                 break;
        }

    }

    public function checkpermissions($type = 'view') {
        if ($this->usuari->es_administrador) {
            return true;
        }

        if ($type === 'edit' || $type === 'editlink') {
            if ($this->usuari->es_administrador or
                ($this->estat == 'obert' and ($this->usuari->es_tutor_centre or $this->usuari->es_tutor_empresa))) {
                    return true;
            } else if ($type === 'editlink') {
                return false;
            } else {
                print_error('nopermissions', 'fct');
            }
        } else {
            parent::checkpermissions($type);
        }
    }

    public function get_object_data() {

        $this->create_llist();

        $objectdata = parent::get_object_data();
        $objectdata->valoracions = $this->valoraciollist;

        $objectdata->valoracio = $this->valoracio;

        return $objectdata;
    }
}