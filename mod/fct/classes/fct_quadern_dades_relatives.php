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
 * Quadern dades relatives FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('form/quadern_dades_relatives_edit_form.php');
require_once('fct_quadern_base.php');
require_once('fct_base.php');
require_once('fct_cicle.php');
require_once('fct_resum_hores.php');

class fct_quadern_dades_relatives extends fct_quadern_base {

    protected $editform = 'fct_quadern_dades_relatives_edit_form';

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);
        $subtree = parent::subtree($id, $this->id);

        $row = $tab['row'];
        $row['quadern_dades']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = 'quadern_dades_relatives';

        return $tab;
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quadern_dades_relatives');
        $output->view($this);

        return true;

    }

    public function resum_hores_fct() {
        global $DB;

        $hores_practiques = 0;

        $quadernsrecords = $DB->get_records('fct_quadern', array('cicle' => $this->cicle, 'alumne' => $this->alumne));

        foreach ($quadernsrecords as $record) {
            $quadern = new fct_quadern_base((int)$record->id);
            if ($quadern->apte != 2) {
                $hores_practiques += $quadern->hores_realitzades_quadern($quadern->id);
            }
        }

        $resum = new fct_resum_hores_fct($this->hores_credit,
                                       $this->hores_anteriors,
                                       $this->exempcio,
                                       $hores_practiques);

        return $resum;
    }

    public function prepare_form_data($data) {
        $data->excempcions = array('0' => '-', '25' => '25', '50' => '50');
    }

    public function checkpermissions($type = 'view') {

        if ($this->usuari->es_administrador) {
            return true;
        }

        if (parent::checkpermissions($type)) {
            if ($type == 'edit' || $type = 'editlink') {
                if ($this->usuari->es_alumne) {
                    if ($type == 'editlink') {
                        return false;
                    } else {
                        print_error('nopermissions', 'fct');
                    }
                }
            }
        } else {
            return false;
        }
        return true;
    }
}