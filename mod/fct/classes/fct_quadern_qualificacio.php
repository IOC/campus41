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
 * Quadern FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_qualificacio_edit_form.php');
require_once('fct_quadern_base.php');
require_once('fct_base.php');
require_once('fct_cicle.php');

class fct_quadern_qualificacio extends fct_quadern_base {


    protected $qualificaciotype = 'parcial';
    protected static $dataobject = 'qualificacio';

    protected $editform = 'fct_quadern_qualificacio_edit_form';

    protected static $dataobjectkeys = array('apte',
                                             'nota',
                                             'data',
                                             'observacions');

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);

        $subtree = array();

        if ($this->qualificaciotype == 'parcial') {
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
            $tab['currentab'] = 'qualificacio_quadern';
            $tab['row'] = $row;
        } else {
            $tab['currentab'] = 'quadern_qualificacio';
        }

        return $tab;
    }

    public function __construct($record = null) {
        if (isset($record->qualificaciotype) && $record->qualificaciotype == 'global') {
            self::$dataobject = 'qualificacio_global';
            parent::__construct($record);
            $this->qualificaciotype = 'global';
        } else {
            parent::__construct($record);
        }

    }

    public function __set($name, $value) {
        if ($name == 'qualificaciotype') {
            self::$dataobject = $value == 'global' ? 'qualificacio_global' : 'qualificacio';
            $this->$name = $value;
        }
    }

    public function __get($name) {
        if ($name == 'qualificaciotype') {
            return $this->qualificaciotype;
        }
        $dataobjectkeys = static::$dataobjectkeys;
        $dataobject = static::$dataobject;

        if (!isset($this->$dataobject)) {
            return false;
        }

        if (array_key_exists($name, array_flip($dataobjectkeys))) {
             return $this->$dataobject->$name;
        }
        return false;
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct',
            'quadern_qualificacio');

        self::__construct((int)$this->id);

        $output->view($this);

        return true;
    }

    public function checkpermissions($type = 'view') {
        if ($this->usuari->es_administrador) {
            return true;
        }

        if ($type === 'edit' || $type == 'editlink') {
            if ($this->usuari->es_tutor_empresa and $this->estat == 'obert' and $this->qualificaciotype == 'global') {
                return false;
            } else if ($this->estat == 'obert' and ($this->usuari->es_tutor_centre or $this->usuari->es_tutor_empresa)) {
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

    public static function validation($data) {
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

    public function qualificacions() {
        return array(
            0 => '-',
            1 => get_string('apte', 'fct'),
            2 => get_string('noapte', 'fct')
        );
    }
}
