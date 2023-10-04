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
 * Quadern dades FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_base.php');
require_once('fct_quadern_base.php');
require_once('fct_dades_centre.php');


class fct_quadern_dades extends fct_quadern_base {

    public $quadern;

    protected static $table = 'fct_quadern';

    public $id;
    public $alumne;
    public $tutor_centre;
    public $tutor_empresa;
    public $nom_empresa;
    public $cicle;
    public $estat;
    public $fct;

    public function __construct($record) {
        if (isset($record->fct)) {
            $this->fct = $record->fct;
        }
        if (isset($record->quadern)) {
            parent::__construct((int)$record->quadern);
        } else {
            parent::__construct($record);
        }
    }

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);
        $subtree = parent::subtree($id, $this->id);

        $row = $tab['row'];
        $row['quadern_dades']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = 'quadern_centre';

        return $tab;
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'centre');
        $fct = new fct_dades_centre((int)$this->fct);

        $centre = $output->centre($fct->centre, false);

        echo $centre;
    }

    public function prepare_form_data($data) {
    }
}
