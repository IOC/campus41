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
 * Resum FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_quadern.php');

class fct_resum_seguiment extends fct_base {

    public $id;
    public $quadern;
    public $resum;
    public $total_hores = 0;
    public $total_dies = 0;

    protected static $table = 'fct_quinzena';

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->quadern);

        $subtree = array();

        $subtree[] = new tabobject('quinzenesllist', new moodle_url('/mod/fct/view.php',
                                        array('id' => $id, 'quadern' => $this->quadern, 'page' => 'quadern_quinzena')),
                                        get_string('quinzenes', 'fct'));
        if (self::checkpermissions('addquinzena')) {
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
        $tab['currentab'] = 'resum_seguiment';
        $tab['row'] = $row;

        return $tab;
    }

    public function view($id = false) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'resum_seguiment');

        if ($quinzenes = fct_quadern_quinzena::get_records($this->quadern)) {
            $this->calcular_resum($quinzenes);
            $output->view($this->resum, $this->total_hores, $this->total_dies);
        } else {
            echo $output->notification(get_string('cap_quinzena', 'fct'));
        }

    }

    public function calcular_resum($quinzenes) {
        $resum = array();

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
        }
        $this->resum = $resum;
    }

    public function checkpermissions($type = 'view') {

        if (!isset($this->quadern)) {
            print_error('noquadern');
        }

        if ($this->usuari->es_administrador) {
            return true;
        }

        $quadern = new fct_quadern($this->quadern);

        if ($type == 'addquinzena') {
            if ($quadern->estat != 'obert' || $this->usuari->es_tutor_empresa) {
                return false;
            }
            return true;
        }

        if ($quadern->estat == 'proposat' && !$this->usuari->es_administrador) {
            print_error('nopermissions', 'fct');
        }

        return true;
    }



    public function prepare_form_data($data) {
    }


}